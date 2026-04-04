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
    Write-Host "Plugin symlink already exists: $PluginTarget" -ForegroundColor Yellow
    return
}

# Phase 4.3: create symlink
Write-Warning "link-plugin.ps1 is a scaffold — real implementation in Phase 4.3."
