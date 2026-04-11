<#
.SYNOPSIS
    Seed pipeline orchestrator - export -> import -> dump -> verify.

.DESCRIPTION
    Runs the full content parity pipeline in one command:
      1. Export seed.json from site.ts (client repo)
      2. Import seed.json into WordPress (ACF update_field)
      3. Dump current WP ACF state to wp-state.json
      4. Verify parity: seed.json vs wp-state.json
      5. Endpoint smoke: /spektra/v1/site image field resolution

.PARAMETER Client
    Client slug (must match sp-clients/sp-<Client> and .local/wp-runtimes/<Client>).
    Default: benettcar.

.PARAMETER DryRun
    Run import in dry-run mode (no WP writes).

.PARAMETER Verbose
    Pass --verbose to all steps.

.PARAMETER SkipExport
    Skip step 1 (use existing seed.json).

.EXAMPLE
    .\seed-pipeline.ps1
    .\seed-pipeline.ps1 -Client benettcar
    .\seed-pipeline.ps1 -Client newclient -Verbose
    .\seed-pipeline.ps1 -DryRun
    .\seed-pipeline.ps1 -SkipExport
#>

param(
    [string]$Client = 'benettcar',
    [switch]$DryRun,
    [switch]$Verbose,
    [switch]$SkipExport
)

$ErrorActionPreference = 'Stop'

# -- Paths ------------------------------------------------------

$SpRoot      = (Resolve-Path "$PSScriptRoot\..\..").Path
$SeedDir     = $PSScriptRoot
$ClientDir   = Join-Path $SpRoot "sp-clients\sp-$Client"
$WpRuntime   = Join-Path $SpRoot ".local\wp-runtimes\$Client"
$SeedJson    = Join-Path $SeedDir 'seed.json'
$WpState     = Join-Path $SeedDir 'wp-state.json'
$ImportPhp   = Join-Path $SeedDir 'import-seed.php'
$DumpPhp     = Join-Path $SeedDir 'dump-acf.php'
$VerifyTs    = Join-Path $SeedDir 'verify-parity.ts'
$EndpointPhp = Join-Path $SeedDir 'verify-endpoint.php'

# PHP - use highest 8.4.x in WAMP (8.5+ breaks WP-CLI phar)
$WampPhpBase = 'D:\Local\wamp\bin\php'
$PhpDir      = Get-ChildItem $WampPhpBase -Directory |
               Where-Object { $_.Name -match '^php8\.4' } |
               Sort-Object Name -Descending |
               Select-Object -First 1
if (-not $PhpDir) {
    # Fallback: highest available version excluding 8.5+
    $PhpDir = Get-ChildItem $WampPhpBase -Directory |
              Where-Object { $_.Name -notmatch '^php8\.5' } |
              Sort-Object Name -Descending |
              Select-Object -First 1
}
$PhpExe      = Join-Path $PhpDir.FullName 'php.exe'

# WP-CLI phar
$WpCliPhar   = Join-Path $SeedDir 'wp-cli.phar'

# -- Helpers ----------------------------------------------------

function Write-Step($num, $label) {
    Write-Host ''
    Write-Host ('=' * 51) -ForegroundColor Cyan
    Write-Host "  Step $num - $label" -ForegroundColor Cyan
    Write-Host ('=' * 51) -ForegroundColor Cyan
    Write-Host ''
}

function Invoke-WpCli {
    param(
        [string[]]$WpArgs,
        [string[]]$ScriptArgs = @()
    )

    if (-not (Test-Path $WpCliPhar)) {
        Write-Host "  WP-CLI not found. Downloading..." -ForegroundColor Yellow
        Invoke-WebRequest -Uri 'https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar' `
            -OutFile $WpCliPhar -UseBasicParsing
        Write-Host "  WP-CLI downloaded -> $WpCliPhar" -ForegroundColor Green
    }

    # Script-specific args go as positional (no -- prefix) so WP-CLI won't intercept them.
    $fullArgs = @($WpCliPhar) + $WpArgs + @("--path=$WpRuntime") + $ScriptArgs
    # PHP/WP-CLI writes deprecation notices + WP_CLI::warning() to stderr.
    # With $ErrorActionPreference = 'Stop', stderr lines from native commands
    # become terminating errors. Temporarily switch to Continue for the PHP call.
    $prevEAP = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    & $PhpExe @fullArgs 2>&1 | ForEach-Object {
        if ($_ -is [System.Management.Automation.ErrorRecord]) {
            Write-Host $_.Exception.Message -ForegroundColor DarkYellow
        } else {
            Write-Host $_
        }
    }
    $ErrorActionPreference = $prevEAP
    if ($LASTEXITCODE -ne 0) {
        throw "WP-CLI failed (exit code $LASTEXITCODE)"
    }
}

# -- Preflight checks ------------------------------------------

Write-Host ""
Write-Host 'Seed Pipeline - Content Parity Bootstrap' -ForegroundColor White
Write-Host ('-' * 41) -ForegroundColor DarkGray

if (-not (Test-Path $PhpExe)) {
    throw "PHP not found at $PhpExe"
}
if (-not (Test-Path $WpRuntime)) {
    throw "WP runtime not found at $WpRuntime"
}
if (-not (Test-Path $ClientDir)) {
    throw "Client repo not found at $ClientDir"
}

Write-Host "  PHP:        $PhpExe" -ForegroundColor DarkGray
Write-Host "  WP runtime: $WpRuntime" -ForegroundColor DarkGray
Write-Host "  Client:     $ClientDir" -ForegroundColor DarkGray
Write-Host "  Seed dir:   $SeedDir" -ForegroundColor DarkGray

# -- Step 1: Export ---------------------------------------------

if ($SkipExport) {
    Write-Host ""
    Write-Host "  [SKIP] Export - using existing seed.json" -ForegroundColor Yellow
    if (-not (Test-Path $SeedJson)) {
        throw "seed.json not found at $SeedJson - cannot skip export"
    }
} else {
    Write-Step 1 "Export seed.json from site.ts"

    Push-Location $ClientDir
    try {
        pnpm seed:export
        if ($LASTEXITCODE -ne 0) { throw "seed:export failed" }
    } finally {
        Pop-Location
    }

    if (-not (Test-Path $SeedJson)) {
        throw "seed.json was not created at $SeedJson"
    }
    Write-Host '  [OK] seed.json generated' -ForegroundColor Green
}

# -- Step 2: Import into WordPress ------------------------------

Write-Step 2 "Import seed.json into WordPress"

$importWpArgs = @('eval-file', $ImportPhp)
$importScriptArgs = @($SeedJson)
if ($DryRun)  { $importScriptArgs += 'dry-run' }
if ($Verbose) { $importScriptArgs += 'verbose' }
# Pass client dir so importer can resolve local asset paths for sideloading
$importScriptArgs += 'client-dir'
$importScriptArgs += $ClientDir

Invoke-WpCli -WpArgs $importWpArgs -ScriptArgs $importScriptArgs

Write-Host '  [OK] Import complete' -ForegroundColor Green

# -- Step 3: Dump WP ACF state ---------------------------------

Write-Step 3 "Dump current WP state to wp-state.json"

$dumpWpArgs = @('eval-file', $DumpPhp)
$dumpScriptArgs = @($SeedJson, 'output', $WpState)
Invoke-WpCli -WpArgs $dumpWpArgs -ScriptArgs $dumpScriptArgs

if (-not (Test-Path $WpState)) {
    throw "wp-state.json was not created at $WpState"
}
Write-Host '  [OK] wp-state.json dumped' -ForegroundColor Green

# -- Step 4: Verify parity -------------------------------------

Write-Step 4 "Verify parity: seed.json vs wp-state.json"

Push-Location $SeedDir
try {
    $verifyArgs = @($VerifyTs, "--seed", $SeedJson, "--state", $WpState)
    if ($Verbose) { $verifyArgs += '--verbose' }

    npx tsx @verifyArgs
    $verifyExit = $LASTEXITCODE
} finally {
    Pop-Location
}

# -- Step 5: Endpoint smoke test --------------------------------

Write-Step 5 "Endpoint smoke: /spektra/v1/site image fields"

$endpointWpArgs = @('eval-file', $EndpointPhp)
$endpointScriptArgs = @()
if ($Verbose) { $endpointScriptArgs += 'verbose' }

Invoke-WpCli -WpArgs $endpointWpArgs -ScriptArgs $endpointScriptArgs

Write-Host '  [OK] Endpoint smoke passed' -ForegroundColor Green

# -- Result -----------------------------------------------------

Write-Host ""
if ($verifyExit -eq 0) {
    Write-Host '  PIPELINE RESULT: PASS' -ForegroundColor Green
} else {
    Write-Host '  PIPELINE RESULT: FAIL (parity)' -ForegroundColor Red
    exit 1
}
