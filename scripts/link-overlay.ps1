<#
.SYNOPSIS
    Junction client infra overlay into WP runtime plugins directory.
    The overlay lands as wp-content/plugins/spektra-config/ so that
    spektra-api.php can load config.php via its symlink fallback path.

.PARAMETER Client
    Client slug. Default: "benettcar".

.EXAMPLE
    .\link-overlay.ps1 -Client benettcar
#>
param(
    [string]$Client = "benettcar"
)

$ErrorActionPreference = "Stop"

$WorkspaceRoot = (Get-Item "$PSScriptRoot\..\..\").FullName
$OverlaySource = Join-Path $WorkspaceRoot "sp-clients\sp-$Client\infra"
$OverlayTarget = Join-Path $WorkspaceRoot ".local\wp-runtimes\$Client\wp-content\plugins\spektra-config"

# --- Guard: source exists? ---
if (-not (Test-Path $OverlaySource)) {
    Write-Error "Overlay source not found: $OverlaySource"
    return
}

# --- Guard: plugins dir exists? ---
$PluginsDir = Split-Path $OverlayTarget
if (-not (Test-Path $PluginsDir)) {
    Write-Error "WP plugins dir not found: $PluginsDir -- is WordPress installed for client '$Client'?"
    return
}

# --- Idempotent: if target exists, validate it points to the correct source ---
if (Test-Path $OverlayTarget) {
    $existing = Get-Item $OverlayTarget
    $existingTarget = $existing.Target
    if ($existingTarget -eq $OverlaySource) {
        Write-Host "Overlay junction already exists and points to correct source: $OverlayTarget" -ForegroundColor Yellow
        return
    } else {
        Write-Error "Overlay junction exists but points to WRONG source!`n  Current target: $existingTarget`n  Expected target: $OverlaySource`n  Remove it manually and re-run."
        return
    }
}

# --- Create Junction ---
New-Item -ItemType Junction -Path $OverlayTarget -Target $OverlaySource | Out-Null
Write-Host "Overlay linked: $OverlayTarget -> $OverlaySource" -ForegroundColor Green
