<?php

    /**
     * Assets
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-view 
     */

    namespace Embryo\Assets;

    use Psr\Http\Message\ServerRequestInterface;
    
    class Assets 
    {
        /**
         * @var array $files
         */
        private $files = [];
        
        /**
         * @var string $type
         */
        private $type;

        /**
         * @var string $filename
         */
        private $filename = 'app';

        /**
         * @var bool $forceBuild
         */
        private $forceBuild = false;

        /**
         * @var ServerRequestInterface $request
         */
        private $request;

        /**
         * Set array files and type assets (css or js).
         * 
         * @param array $files 
         * @param string $type 
         * @throws \InvalidArgumentException
         */
        public function __construct(array $files, string $type)
        {
            if (!in_array($type, ['css', 'js'])) {
                throw new \InvalidArgumentException('Files must be CSS or JavaScript.');
            }
            $this->files = $files;
            $this->type  = $type;
        }

        /**
         * Set PSR Server Request.
         * 
         * @param ServerRequestInterface $request 
         * @return self
         */
        public function setRequest(ServerRequestInterface $request): self
        {
            $this->request = $request;
            return $this;
        }

        /**
         * Set name of output bundle.
         * Default filename is 'app'.
         * 
         * @param string $filename 
         * @return self
         */
        public function setFilename(string $filename = 'app'): self
        {
            $this->filename = $filename;
            return $this;
        }

        /**
         * Force build.
         * 
         * @param bool $forceBuild
         * @return self
         */
        public function forceBuild(bool $forceBuild): self
        {
            $this->forceBuild = $forceBuild;
            return $this;
        }

        /**
         * Combine and minify files.
         * 
         * @return string
         */
        public function build(string $compilersPath): string
        {
            $compilersPath = rtrim($compilersPath, DIRECTORY_SEPARATOR);
            $app           = $compilersPath.DIRECTORY_SEPARATOR.$this->filename.'.'.$this->type;
            $map           = $compilersPath.DIRECTORY_SEPARATOR.$this->filename.'.'.$this->type.'.map';
            $build         = false;
            $code          = '';
            $document_root = $this->request->getServerParams()['DOCUMENT_ROOT'];

            if (!file_exists($map) || !file_exists($app)) {
                $build = true;
            }

            if (file_exists($map) && file_get_contents($map)) {
                $contentMap = file_get_contents($map);
                $sourcesMap = $contentMap ? json_decode($contentMap) : [];
                if ($sourcesMap != $this->files) {
                    $build = true;
                }
            }

            if (file_exists($app)) {
                foreach ($this->files as $file) {
                    $f = $file;
                    if (file_exists($f)) {
                        if (filemtime($f) > filemtime($app)) {
                            $build = true;
                        }
                    }
                }
            }

            if ($build || $this->forceBuild) {
                
                foreach ($this->files as $file) {
                    $f = $file;
                    if (file_exists($f)) {
                        switch($this->type) {
                            case 'css': 
                                $css = $this->minify($f, 'css'); 
                                $code .= $this->resolveRelativePath($css, $f);
                            break;
                            case 'js': 
                                $code .= $this->minify($f, 'js');
                            break;       
                        }
                    }
                }
                $files = json_encode(str_replace($document_root, '', $this->files));
                file_put_contents($map, $files);
                file_put_contents($app, $code);

            } else {
                $appContent = file_get_contents($app);
                $code = $appContent ? $appContent : '';
            }
            return $code;
        }

        /**
         * Set css files.
         * 
         * @param array $files 
         * @return self
         */
        public static function css(array $files): self
        {
            return new Assets($files, 'css');
        }
        
        /**
         * Set javascript files.
         * 
         * @param array $files 
         * @return self
         */
        public static function js(array $files): self
        {
            return new Assets($files, 'js');
        }

        /**
         * Minify CSS or JS file.
         * 
         * @see https://cssminifier.com/php
         * @see https://javascript-minifier.com/php
         * @param string $file 
         * @param string $type 
         * @return string
         */
        private function minify(string $file, string $type): string
        {
            $host     = ['css' => 'cssminifier', 'js' => 'javascript-minifier'];
            $url      = 'https://'.$host[$type].'.com/raw';
            $content  = file_get_contents($file);
            $input    = $content ? $content : '';
            $ch       = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => ["Content-Type: application/x-www-form-urlencoded"],
                CURLOPT_POSTFIELDS     => http_build_query([ "input" => $input ])
            ]);

            $minified = curl_exec($ch);
            curl_close($ch);
            return strval($minified);
        }

        /**
         * Replace relative path with 
         * root public path.
         * 
         * @param string $css 
         * @param string $source 
         * @return string
         */
        private function resolveRelativePath(string $css, string $source): string
        {
            $dir    = dirname($source);
            $re     = '/url\(\s*?(?P<path>.+?)\s*\)/ix';
            $params = $this->request->getServerParams();
            preg_match_all($re, $css, $matches, PREG_SET_ORDER);
            if (!empty($matches)) {
                foreach ($matches as $match) {
                    $realpath   = realpath($dir.DIRECTORY_SEPARATOR.$match['path']);
                    $publicpath = $realpath ? str_replace($params['DOCUMENT_ROOT'], '', $realpath) : $match['path'];
                    $sub        = 'url('.$publicpath.')';
                    $css        = str_replace($match[0], $sub, $css);
                }
            }
            return $css;
        }
    }