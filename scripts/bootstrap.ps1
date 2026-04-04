<# 
.SYNOPSIS
    Bootstrap local WP runtime for a Spektra client.

.DESCRIPTION
    Creates the .local/wp-runtimes/<client>/ directory, sets up symlinks
    for plugin and client overlay, and verifies the environment.
    
    Phase 4 scripts (P4.3, P4.4) will fill in the real logic.

.PARAMETER Client
    Client slug (e.g., "benettcar"). Default: "benettcar".

.EXAMPLE
    .\bootstrap.ps1 -Client benettcar
#>
param(
    [string]$Client = "benettcar"
)

$ErrorActionPreference = "Stop"

$WorkspaceRoot = (Get-Item "$PSScriptRoot\..\..\").FullName
$RuntimePath = Join-Path $WorkspaceRoot ".local\wp-runtimes\$Client"

Write-Host "Spektra Bootstrap — Client: $Client" -ForegroundColor Cyan
Write-Host "Workspace: $WorkspaceRoot"
Write-Host "Runtime:   $RuntimePath"

# Phase 4: real implementation
# - Create runtime directory
# - Download/copy WordPress core
# - Symlink plugin (link-plugin.ps1)
# - Symlink client overlay (link-overlay.ps1)
# - Verify ACF installed
# - Run setup-env.ps1

Write-Warning "bootstrap.ps1 is a scaffold — real implementation in Phase 4."
