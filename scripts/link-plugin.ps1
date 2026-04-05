<#
.SYNOPSIS
    Symlink sp-infra/plugin/spektra-api/ into WP runtime plugins directory.

.PARAMETER Client
    Client slug. Default: "benettcar".

.EXAMPLE
    .\link-plugin.ps1 -Client benettcar
#>
param(
    [string]$Client = "benettcar"
)

$ErrorActionPreference = "Stop"

$WorkspaceRoot = (Get-Item "$PSScriptRoot\..\..\").FullName
$PluginSource = Join-Path $WorkspaceRoot "sp-infra\plugin\spektra-api"
$PluginTarget = Join-Path $WorkspaceRoot ".local\wp-runtimes\$Client\wp-content\plugins\spektra-api"

if (Test-Path $PluginTarget) {
    Write-Host "Plugin link already exists: $PluginTarget" -ForegroundColor Yellow
    return
}

if (-not (Test-Path $PluginSource)) {
    Write-Error "Plugin source not found: $PluginSource"
    return
}

$PluginsDir = Split-Path $PluginTarget
if (-not (Test-Path $PluginsDir)) {
    Write-Error "WP plugins dir not found: $PluginsDir -- is WordPress installed for client '$Client'?"
    return
}

New-Item -ItemType Junction -Path $PluginTarget -Target $PluginSource | Out-Null
Write-Host "Plugin linked: $PluginTarget -> $PluginSource" -ForegroundColor Green
