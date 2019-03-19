<?php
/**
 * Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Alonity;

use Framework\Alonity\DI\DI;
use Framework\Alonity\Router\RouterHelper;
use Framework\Alonity\Triggers\Triggers;
use Framework\Alonity\Triggers\TriggersException;

class Alonity implements AlonityInterface {

	use Triggers;

	// Версия ядра
	const VERSION = 'rc-1.0.1';

	// Версия приложения
	private $AppVersion = null;

	private $AppKey = null;

	// Описание приложения
	private $AppAbout = null;

	// Сайт автора приложения
	private $AppSite = null;

	// Автор приложения
	private $AppAuthor = null;

	// Путь до главного файла приложения
	private $AppFilename = null;

	// Пространство имен приложения
	private $AppNamespace = null;

	// Класс приложения
	private $AppClass = null;

	// Метод приложения
	private $AppMethod = null;

	// Экземпляр класса приложения
	private $AppObject = null;

	private $rootDir = null;

	private $application = null;

	public function __construct(){
		if(!DI::has('ALONITY')){
			DI::set('ALONITY', $this);
		}
	}

	/**
	 * Получение конфигка приложения
	 *
	 * @return array
	*/
	final public function getApplication(){
		if(!is_null($this->application)){
			return $this->application;
		}

		$filename = $this->getRoot() . '/Application.php';

		if(!file_exists($filename)){
			exit('Application.php not found');
		}

		$this->application = (require($filename));

		return $this->application;
	}

	/**
	 * Выставляет корневую директорию
	 *
	 * @param $root string
	*/
	public function setRoot($root){
		$this->rootDir = $root;
	}

	/**
	 * Производит поиск корневой директории
	 *
	 * @return string|null
	*/
	private function searchRoot(){
		$result = null;

		$path = __DIR__;

		for($i=0;$i<10;$i++){
			if(!file_exists("{$path}/vendor")){
				$path = dirname($path);
			}else{
				$result = $path;
				break;
			}
		}

		return $result;
	}

	/**
	 * Возвращает директорию корня сайта
	 *
	 * @return string
	 */
	public function getRoot(){
		if(!is_null($this->rootDir)){ return $this->rootDir; }

		$search = $this->searchRoot();

		$this->rootDir = (is_null($search)) ? dirname(__DIR__) : $search;

		return $this->rootDir;
	}

	/**
	 * Получение массива параметров приложения
	 *
	 * @return array
	 */
	public function getApp(){
		return RouterHelper::getAppConfig();
	}

	/**
	 * Возвращает версию приложения
	 *
	 * @return string
	*/
	public function getVersion(){
		if(!is_null($this->AppVersion)){
			return $this->AppVersion;
		}

		$app = $this->getApp();

		if(isset($app['app_version'])){
			$this->AppVersion = $app['app_version'];
		}

		return $this->AppVersion;
	}

	/**
	 * Возвращает информацию о приложении
	 *
	 * @return string
	 */
	public function getAbout(){
		if(!is_null($this->AppAbout)){
			return $this->AppAbout;
		}

		$app = $this->getApp();

		if(isset($app['app_description'])){
			$this->AppAbout = $app['app_description'];
		}

		return $this->AppAbout;
	}

	/**
	 * Возвращает автора приложения
	 *
	 * @return string
	 */
	public function getAuthor(){
		if(!is_null($this->AppAuthor)){
			return $this->AppAuthor;
		}

		$app = $this->getApp();

		if(isset($app['app_author'])){
			$this->AppAuthor = $app['app_author'];
		}

		return $this->AppAuthor;
	}

	/**
	 * Возвращает автора приложения
	 *
	 * @return string
	 */
	public function getSite(){
		if(!is_null($this->AppSite)){
			return $this->AppSite;
		}

		$app = $this->getApp();

		if(isset($app['app_site'])){
			$this->AppSite = $app['app_site'];
		}

		return $this->AppSite;
	}

	/**
	 * Возвращает ключ приложения
	 *
	 * @return string
	 */
	public function getKey(){

		if(!is_null($this->AppKey)){
			return $this->AppKey;
		}

		$app = $this->getApplication();

		$this->AppKey = $app['app_name'];

		return $this->AppKey;
	}

	/**
	 * Возвращает имя приложения
	 *
	 * @deprecated
	 *
	 * @return string
	 */
	public function getName(){
		return $this->getKey();
	}

	/**
	 * Возвращает путь к главному файлу приложения
	 *
	 * @return string
	 */
	public function getFilename(){

		if(!is_null($this->AppFilename)){
			return $this->AppFilename;
		}

		$app = $this->getApplication();

		if(isset($app['app_filename'])){
			$this->AppFilename = $app['app_filename'];
		}else{
			$this->AppFilename = "/Applications/{$app['app_name']}/{$app['app_name']}.php";
		}

		return $this->AppFilename;
	}

	/**
	 * Возвращает пространство имен приложения
	 *
	 * @return string
	 */
	public function getNamespace(){

		if(!is_null($this->AppNamespace)){
			return $this->AppNamespace;
		}

		$app = $this->getApplication();

		if(isset($app['app_namespace'])){
			$this->AppNamespace = $app['app_namespace'];
		}else{
			$this->AppNamespace = "Framework\\Applications\\".$app['app_name'];
		}

		return $this->AppNamespace;
	}

	/**
	 * Возвращает имя класса приложения
	 *
	 * @return string
	 */
	public function getClass(){

		if(!is_null($this->AppClass)){
			return $this->AppClass;
		}

		$app = $this->getApplication();

		if(isset($app['app_class'])){
			$this->AppClass = $app['app_class'];
		}else{
			$this->AppClass = $app['app_name'];
		}

		return $this->AppClass;
	}

	/**
	 * Возвращает имя метода активатора приложения
	 *
	 * @return string
	 */
	public function getMethod(){

		if(!is_null($this->AppMethod)){
			return $this->AppMethod;
		}

		$app = $this->getApplication();

		$this->AppMethod = (isset($app['app_method'])) ? $app['app_method'] : 'execute';

		return $this->AppMethod;
	}

	/**
	 * Возвращает экземпляр класса приложения
	 *
	 * @return string
	 */
	public function getAppObject(){

		if(!is_null($this->AppObject)){
			return $this->AppObject;
		}

		$classname = $this->getNamespace().'\\'.$this->getClass();

		if(class_exists($classname)){
			$this->AppObject = new $classname();
		}

		return $this->AppObject;
	}

	public function run(){

		try{
			$this->callToTrigger('onBeforeConstructApp');
		}catch (TriggersException $e){
			exit($e->getMessage());
		}

		$app = $this->getAppObject();

		try{
			$this->callToTrigger('onAfterConstructApp');
		}catch (TriggersException $e){
			exit($e->getMessage());
		}

		$method = $this->getMethod();

		try{
			$this->callToTrigger('onBeforeCallApp');
		}catch (TriggersException $e){
			exit($e->getMessage());
		}

		$app->$method();

		try{
			$this->callToTrigger('onAfterCallApp');
		}catch (TriggersException $e){
			exit($e->getMessage());
		}

		$route = RouterHelper::getCurrent();

		if(isset($route['view']) && !empty($route['view']) && !is_null($route['view'])){
			echo $route['view'];

			return;
		}

		try{
			$this->callToTrigger('onBeforeCallController');
		}catch (TriggersException $e){
			exit($e->getMessage());
		}

		$classname = (is_null($route['namespace'])) ? $route['controller'] : $route['namespace'].'\\'.$route['controller'];

		$call = new $classname();

		try{
			$this->callToTrigger('onAfterCallController');
		}catch (TriggersException $e){
			exit($e->getMessage());
		}

		try{
			$this->callToTrigger('onBeforeCallAction');
		}catch (TriggersException $e){
			exit($e->getMessage());
		}

		$action = (is_null($route['action'])) ? 'index' : $route['action'];

		$call->$action();
	}
}

?>