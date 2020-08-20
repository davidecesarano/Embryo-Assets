<?php 

    require __DIR__ . '/../vendor/autoload.php';
    
    use Embryo\Assets\Assets;

    $assetsPath = __DIR__.DIRECTORY_SEPARATOR.'assets';
    $outputPath = __DIR__.DIRECTORY_SEPARATOR;

    Assets::css([
        $assetsPath.'/css/style1.css',
        $assetsPath.'/css/style2.css'
    ])
    ->build($outputPath)
    ->inline();