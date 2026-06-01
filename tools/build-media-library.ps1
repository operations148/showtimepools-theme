<#
  build-media-library.ps1
  ------------------------------------------------------------------
  Builds a clean "best-of" media library for showtimepools.com from
  Steve's curated portfolio on the Drive.

  WHAT IT DOES
   - Copies only the curated "Showtime Pools NN" originals (skips the
     raw IMG-*-WA####.jpg phone dumps and the TASKS job folders).
   - Organizes them by category into C:\xampp\htdocs\showtimepools\media\
   - Also pulls the logo + brand graphics into media\brand\

  HOW TO RUN
   1. Make sure Google Drive is online and the IT'S SHOWTIME! folder
      shows real files (not "online only" cloud icons). If unsure,
      right-click the folder in Explorer -> "Available offline" first.
   2. Right-click this file -> "Run with PowerShell"
      (or open PowerShell and run:  powershell -ExecutionPolicy Bypass -File "build-media-library.ps1")

  SAFE TO RE-RUN: it only copies, never deletes. Re-running just
  refreshes/updates files.
#>

$source = "C:\Official Drive\Showtime\My Drive\IT'S SHOWTIME!"
$dest   = "C:\xampp\htdocs\showtimepools\media"

# Drive category folder  ->  tidy media subfolder
$map = [ordered]@{
    "POOLS"                = "pools"
    "EQUIPMENT & PLUMBING" = "equipment-plumbing"
    "SPA"                  = "spa"
    "REPAIR"               = "repair"
    "REMODELING"           = "remodeling"
    "WEEKLY CLEANING"      = "weekly-cleaning"
}

if (-not (Test-Path $source)) {
    Write-Host "ERROR: Cannot see the source folder:" -ForegroundColor Red
    Write-Host "  $source"
    Write-Host "Make sure Google Drive is running and the folder is downloaded (not online-only)."
    exit 1
}

Write-Host "Building media library at: $dest" -ForegroundColor Cyan

foreach ($folder in $map.Keys) {
    $src = Join-Path $source $folder
    $dst = Join-Path $dest   $map[$folder]
    if (Test-Path $src) {
        New-Item -ItemType Directory -Force -Path $dst | Out-Null
        # /S includes the REMODELING\REPLUMBING subfolder. Filter copies
        # ONLY the curated "Showtime Pools NN.jpg" portfolio shots.
        robocopy $src $dst "Showtime Pools *.jpg" /S /NFL /NDL /NJH /NJS /NP | Out-Null
        $count = (Get-ChildItem -Path $dst -Recurse -Filter "Showtime Pools *.jpg" -ErrorAction SilentlyContinue).Count
        Write-Host ("  {0,-20} -> {1,-20} {2} photos" -f $folder, $map[$folder], $count)
    }
}

# Brand assets: logo + marketing graphics from OTHERS
$brand = Join-Path $dest "brand"
New-Item -ItemType Directory -Force -Path $brand | Out-Null
robocopy (Join-Path $source "OTHERS") $brand "Showtime Pools*.png" /NFL /NDL /NJH /NJS /NP | Out-Null
$brandCount = (Get-ChildItem -Path $brand -Filter "*.png" -ErrorAction SilentlyContinue).Count
Write-Host ("  {0,-20} -> {1,-20} {2} files" -f "OTHERS (brand)", "brand", $brandCount)

$total = (Get-ChildItem -Path $dest -Recurse -File -ErrorAction SilentlyContinue).Count
Write-Host ""
Write-Host "Done. $total files copied into $dest" -ForegroundColor Green
