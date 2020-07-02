<?php

    /**
     * Assets
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-view 
     */

    namespace Embryo\Assets;

    class Assets 
    {
        /**
         * @var array $files
         */
        public $files = [];
        
        /**
         * @var string $type
         */
        public $type;

        /**
         * @var string $filename
         */
        public $filename = 'app';

        /**
         * @var string $assetsPath
         */
        public $assetsPath = '';

        /**
         * @var string $compilersPath
         */
        public $compilersPath = '';

        /**
         * @var string $absolutePath
         */
        public $absolutePath = '';

        /**
         * @var string $bundle
         */
        public $bundle;

        /**
         * Set array files and type assets (css or js).
         * 
         * @param array $files 
         * @param string $type 
         * @return self
         * @throws InvalidArgumentException
         */
        public function __construct(array $files, string $type)
        {
            if (!in_array($type, ['css', 'js'])) {
                throw new \InvalidArgumentException('Files must be a css or js');
            }
            $this->files = $files;
            $this->type  = $type;
            return $this;
        }

        /**
         * Set name of output bundle.
         * Default filename is 'app'.
         * 
         * @param string $filename 
         * @return self
         */
        public function setFilename(string $filename = 'app')
        {
            $this->filename = $filename;
            return $this;
        }

        /**
         * Set assets path.
         * 
         * @param string $assetsPath 
         * @return self
         */
        public function setAssetsPath(string $assetsPath)
        {
            $this->assetsPath = rtrim($assetsPath, DIRECTORY_SEPARATOR);
            return $this;
        }

        /**
         * Set compilers path.
         * 
         * @param string $compilersPath 
         * @return self
         */
        public function setCompilersPath(string $compilersPath)
        {
            $this->compilersPath = rtrim($compilersPath, DIRECTORY_SEPARATOR);
            return $this;
        }

        /**
         * Set absolute path for replacing the relative
         * path in css file.
         * 
         * @param $absolutePath 
         * @return self
         */
        public function resolveRelativePath(string $absolutePath) 
        {
            $this->absolutePath = rtrim($absolutePath, DIRECTORY_SEPARATOR).'/';
            return $this;
        }

        /**
         * Combine and minify files.
         * 
         * @return self
         */
        public function build()
        {
            $app     = $this->compilersPath.'/'.$this->filename.'.'.$this->type;
            $map     = $this->compilersPath.'/'.$this->filename.'.'.$this->type.'.map';
            $build   = false;
            $code    = '';
            $sources = [];

            if (!file_exists($map) || !file_exists($app)) {
                $build = true;
            }

            if (file_exists($map)) {
                $sourcesMap = json_decode(file_get_contents($map));
                if ($sourcesMap != $this->files) {
                    $build = true;
                }
            }

            if (file_exists($app)) {
                foreach ($this->files as $file) {
                    $f = $this->assetsPath.'/'.trim($file, '/');
                    if (file_exists($f)) {
                        if (filemtime($f) > filemtime($app)) {
                            $build = true;
                        }
                    }
                }
            }

            if ($build) {
                
                foreach ($this->files as $file) {
                    $f = $this->assetsPath.'/'.trim($file, '/');
                    if (file_exists($f)) {
                        switch($this->type) {
                            case 'css': 
                                $css  = $this->minify($f, 'css'); 
                                $code .= ($this->absolutePath != '') ? str_replace('../', $this->absolutePath, $css) : $css;
                            break;
                            case 'js': 
                                $code .= $this->minify($f, 'js');
                            break;       
                        }
                    }
                }
                file_put_contents($map, json_encode($this->files));
                file_put_contents($app, $code);

            } else {
                $code = file_get_contents($app);
            }

            $this->bundle = $app;
            $this->code   = $code;
            return $this;
        }

        /**
         * Echo bundle file in html tag.
         * 
         * @return string
         */
        public function bundle()
        {
            if ($this->type === 'css') {
                echo '<link rel="stylesheet" src="'.$this->bundle.'" />';
            } else {
                echo '<script src="'.$this->bundle.'"></script>';
            }
        }

        /**
         * Echo inline files content in html tag.
         * 
         * @return string
         */
        public function inline()
        {
            if ($this->type === 'css') {
                echo '<style>'.PHP_EOL;
                echo $this->code.PHP_EOL;
                echo '</style>';
            } else {
                echo '<script>'.PHP_EOL;
                echo $this->code.PHP_EOL;
                echo '</script>';
            }
        }

        /**
         * Set css files.
         * 
         * @param array $files 
         * @return self
         */
        public static function css(array $files)
        {
            return new Assets($files, 'css');
        }
        
        /**
         * Set javascript files.
         * 
         * @param array $files 
         * @return self
         */
        public static function js(array $files)
        {
            return new Assets($files, 'js');
        }

        /**
         * Minify CSS or JS file.
         * 
         * @see https://cssminifier.com/php
         * @see https://javascript-minifier.com/php
         * @param string $file 
         * @param string type 
         * @return string
         */
        private function minify(string $file, string $type)
        {
            $host    = ['css' => 'cssminifier', 'js' => 'javascript-minifier'];
            $url     = 'https://'.$host[$type].'.com/raw';
            $content = file_get_contents($file);
            $ch      = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => ["Content-Type: application/x-www-form-urlencoded"],
                CURLOPT_POSTFIELDS     => http_build_query([ "input" => $content ])
            ]);

            $minified = curl_exec($ch);
            curl_close($ch);
            return $minified;
        }

        /**
         * Replace relative path with absolute path.
         * 
         * @param string $css 
         * @param string $path 
         * @return string
         */
        private static function resolvePath(string $css, string $path)
        {
            return str_replace('../', $path, $css);
        }
    }