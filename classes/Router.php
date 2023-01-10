<?php
	class Router
	{
		
		public static function get($path,$arg){
			if(empty($_POST)){
			$url = @$_GET['url'];
			if($url == $path){
				$arg();
				return true;
			}

			$path = explode('/',$path);
			$url = explode('/',@$_GET['url']);
			$ok = true;
			$par = [];
			if(count($path) == count($url)){

				foreach ($path as $key => $value) {
					if($value == '?'){
						if($url[$key] === '')
							return;
							$par[$key] = $url[$key];
					}else if($url[$key] != $value){
						$ok = false;
						break;
					}
				}
				if($ok){
					$arg($par);
					return true;
				}

			}
			}
		}


		public static function post($path,$arg){
			if(!empty($_POST)){
			$url = @$_GET['url'];
			if($url == $path){
				$arg();
				return true;
			}

			$path = explode('/',$path);
			$url = explode('/',@$_GET['url']);
			$ok = true;
			$par = [];
			if(count($path) == count($url)){

				foreach ($path as $key => $value) {
					if($value == '?'){
						if($url[$key] === '')
							return;
						$par[$key] = $url[$key];
					}else if($url[$key] != $value){
						$ok = false;
						break;
					}
				}
				if($ok){
					$arg($par);
					return true;
				}

				}
			}
		}
	}
?>