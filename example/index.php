<?php 

    require __DIR__ . '/../vendor/autoload.php';
    use Embryo\Assets\Assets;
    use Embryo\Http\Factory\ServerRequestFactory;

    $request = (new ServerRequestFactory)->createServerRequestFromServer();
    $assetsPath = __DIR__.DIRECTORY_SEPARATOR.'assets';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        <?php 
            echo Assets::css([
                $assetsPath.'/css/style1.css',
                $assetsPath.'/css/style2.css'
            ])
            ->setRequest($request)
            ->build($assetsPath);
        ?>
    </style>
</head>
<body>
    <p>Hello World!</p>
    <script>
        <?php 
            echo Assets::js([
                $assetsPath.'/js/script1.js',
                $assetsPath.'/js/script2.js'
            ])
            ->setRequest($request)
            ->build($assetsPath);
        ?>
    </script>
</body>
</html>