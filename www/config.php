<?php
session_cache_limiter(false);
session_start();
date_default_timezone_set('America/Mexico_City');
// CONFIG
define('SYS_TITLE', 'busTec');
define('SYS_NAME', 'busTec');
define('SYS_DEBUG', true);

// FOLDERS
define('ASSETS_DIR', 'html/');
define('CONTROLLERS_DIR', 'controllers/');
define('VIEWS_DIR', 'views/');
define('MODELS_DIR', 'models/');

#define('OPCIONES', array(PDO::PGSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
define('DB_PREFIX', '');
define('DB_COLLATION', 'utf8_general_ci');
define('DB_CHARSET', 'utf8');
// Slim Vars
define('COOKIE_PREFIX', 'citas');
define('COOKIES_ENABLED', true);
define('COOKIE_SECRET', 'kjsfhuoasfohuasfjno');
define('COOKIE_DURATION', '50 minutes');
define('COOKIE_NAME', COOKIE_PREFIX.SYS_TITLE);
define('SLIM_MODE','development');//development,production


$app = new \Slim\Slim();
if(COOKIES_ENABLED) {
  $app->add(new \Slim\Middleware\SessionCookie(array(
    'secret'  => md5(COOKIE_SECRET),
    'expires' => COOKIE_DURATION,
    'name'    => COOKIE_NAME
  )));
}


$app->config(array(
  'debug' => true,
  'templates.path' => VIEWS_DIR,
  'view' => new \Slim\Views\Twig(),
  'mode' => SLIM_MODE
));

$app->configureMode('production', function () use ($app) {
  $app->config(array('log.enabled' => true, 'debug' => false));
});

$app->configureMode('development', function () use ($app) {
  $app->config(array('log.enabled' => false, 'debug' => true));
});

$app->notFound(function () use ($app) {
  $app->render('errors/404.twig');
});
$app->error(function (\Exception $e) use ($app) {
  $app->render('errors/500.twig');
});

$app->session = $_SESSION;
$app->flash = (isset($_SESSION['slim.flash'])) ? $_SESSION['slim.flash'] : null;

$rootUri = $app->request()->getRootUri();
$assetUri = $rootUri;
$resourceUri = $_SERVER['REQUEST_URI'];
$parts = explode("/",$app->request()->getPathInfo());

$view = $app->view();
$app->view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
    new Twig_Extension_Debug()
);
$app->view()->appendData(array(
  'title' => SYS_TITLE,
  'name' => SYS_NAME,
  'app' => $app,
  'root' => $rootUri,
  'home' => "$rootUri/{$parts[1]}/",
  'assetUri' => $assetUri,
  'activeUrl' => $resourceUri,
  'flash' => $app->flash
));

$twig = $app->view()->getEnvironment();
$twig->addFunction(new Twig_SimpleFunction('asset', function ($asset) use($app) {
    $ext = pathinfo($asset, PATHINFO_EXTENSION);
    $base = $app->request()->getRootUri();
    switch ($ext) {
      case 'css':
        $dir = $base.'/web/css';
        break;
      case 'js':
        $dir = $base.'/web/js';
        break;
      case 'jpeg':
      case 'jpg':
      case 'png':
      case 'gif':
        $dir = $base.'/web/img';
        break;
      case 'mp4':
        $dir = $base.'/web/video';
        break;
      default:
        $dir = $base;
        break;
    }
    return sprintf("$dir/%s", ltrim($asset, '/'));
}));
$twig->addFunction(new Twig_SimpleFunction('active', function ($name) use ($app) {
  $url = $app->urlFor($name);
  $root = $app->request->getRootUri();
  $url = str_replace($root, '', $url);
  //$parts = explode("/",$app->request()->getPathInfo(),-1);
  if ($url === $app->request->getResourceUri()) {
    if (isset($app->active_link_snippet)) {
      return $app->active_link_snippet;
    } else {
      return ' class="active"';
    }
  }
  return '';
  },
  array(
    'is_safe' => array('html')
  )
));
$twig->addExtension(new Twig_Extension_Debug());
$twig->addFilter(new Twig_SimpleFilter('filesize', function ($fs, $digits = 2) use($app){
  $sizes = array("TB", "GB", "MB", "KB", "B");
  $total = count($sizes);
  while ($total-- && $fs > 1024) {
    $fs /= 1024;
  }
  return round($fs, $digits) . " " . $sizes[$total];
}));

// DATABASE
$db = null;
$db = new PDO('sqlite:busTec.sqlite3') or die('Unable to open database');
$app->db = $db;
$app->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$app->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$app->session = $_SESSION;
foreach(glob(MODELS_DIR.'*.php') as $model) {
  require_once $model;
}


?>
