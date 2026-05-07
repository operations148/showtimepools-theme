# Regenerate favicon variants from the source logo. Run this whenever
# showtime-pools-child/assets/img/logo.png changes. Outputs to
# showtime-pools-child/assets/img/favicons/. Windows / PowerShell 5.1+ /
# .NET System.Drawing — no external deps required.
#
# Usage (from repo root):
#   pwsh -File tools/generate-favicons.ps1

$repoRoot = Split-Path -Parent $PSScriptRoot
$src      = Join-Path $repoRoot "showtime-pools-child\assets\img\logo.png"
$outDir   = Join-Path $repoRoot "showtime-pools-child\assets\img\favicons"

if (-not (Test-Path $src)) {
    Write-Error "Source logo not found: $src"
    exit 1
}
New-Item -ItemType Directory -Path $outDir -Force | Out-Null

Add-Type -AssemblyName System.Drawing
$srcImg = [System.Drawing.Image]::FromFile($src)

$sizes = @(
    @{ name = "favicon-16.png";          size = 16  },
    @{ name = "favicon-32.png";          size = 32  },
    @{ name = "favicon-48.png";          size = 48  },
    @{ name = "apple-touch-icon.png";    size = 180 },
    @{ name = "android-chrome-192.png";  size = 192 },
    @{ name = "android-chrome-512.png";  size = 512 }
)

foreach ($spec in $sizes) {
    $bmp = New-Object System.Drawing.Bitmap $spec.size, $spec.size
    $g   = [System.Drawing.Graphics]::FromImage($bmp)
    $g.InterpolationMode  = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.SmoothingMode      = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.PixelOffsetMode    = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $g.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
    $g.DrawImage($srcImg, 0, 0, $spec.size, $spec.size)
    $bmp.Save((Join-Path $outDir $spec.name), [System.Drawing.Imaging.ImageFormat]::Png)
    $g.Dispose()
    $bmp.Dispose()
    Write-Host "wrote $($spec.name) ($($spec.size)x$($spec.size))"
}
$srcImg.Dispose()
Write-Host "Done. Re-deploy to publish."
