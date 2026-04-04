<#
.SYNOPSIS
    Load environment variables for Spektra local development.

.DESCRIPTION
    Reads .env file from client overlay and sets environment variables.
    Primary use: SPEKTRA_CLIENT_CONFIG path for plugin config loading.

.PARAMETER Client
    Client slug. Default: "benettcar".

.EXAMPLE
    .\setup-env.ps1 -Client benettcar
#>
param(
    [string]$Client = "benettcar"
)

$ErrorActionPreference = "Stop"

$WorkspaceRoot = (Get-Item "$PSScriptRoot\..\..\").FullName
$EnvFile = Join-Path $WorkspaceRoot "sp-clients\sp-$Client\infra\env\.env"

if (Test-Path $EnvFile) {
    Write-Host "Loading env from: $EnvFile" -ForegroundColor Cyan
    # Phase 5.4: parse .env file and set environment variables
} else {
    Write-Host "No .env file found at: $EnvFile" -ForegroundColor Yellow
}

Write-Warning "setup-env.ps1 is a scaffold -- real implementation in Phase 5.4."
