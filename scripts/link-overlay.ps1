<#
.SYNOPSIS
    Symlink client infra overlay into WP runtime plugins directory.

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

if (Test-Path $OverlayTarget) {
    Write-Host "Overlay symlink already exists: $OverlayTarget" -ForegroundColor Yellow
    return
}

# Phase 4.4: create symlink
Write-Warning "link-overlay.ps1 is a scaffold -- real implementation in Phase 4.4."
