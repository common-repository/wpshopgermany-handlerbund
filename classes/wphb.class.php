<?php
	
	require_once dirname(__FILE__).'/wphb_db.class.php';

	/**
	 * Klasse, die den Abgleich mit dem Händlerbund übernimmt
	 * @author roger 
	 */
	class wphb 
	{
		
		private $db;
		private $prefix;
		
		public function __construct()
		{
			
			global $wpdb;
			
			if (defined('MULTISITE') && MULTISITE === true)
			{
			
				$this->prefix = $wpdb->base_prefix;
			
			}
			else
			{
			
				$this->prefix = $wpdb->prefix;
			
			}
			
			$this->db = new wphb_db();
			
		}
		
		public function dispatch()
		{
			
			if (isset($_REQUEST['form_submit']))
			{
			
				$this->saveForm();
			
			}
			
			echo $this->render('views/fullform.phtml');
			
		} // public function dispatch()
		
		public function wp_loaded()
		{
			
			if (isset($_REQUEST['wphb_submit']))
			{
				
				$this->saveForm();
				
			}
			
		}
		
		public function saveForm()
		{
			
			$this->update_option('wphb_accessToken', $_REQUEST['wphb_accessToken']);
			
			if (isset($_REQUEST['wpsg_page_battery'])) $this->createPage(__('Batteriehinweise', 'wpsg'), 'wpsg_page_battery', $_REQUEST['wpsg_page_battery']);
			if (isset($_REQUEST['wpsg_page_versand'])) $this->createPage(__('Versandkosten', 'wpsg'), 'wpsg_page_versand', $_REQUEST['wpsg_page_versand']);
			if (isset($_REQUEST['wpsg_page_agb'])) $this->createPage(__('Allgemeine Geschäftsbedingungen', 'wpsg'), 'wpsg_page_agb', $_REQUEST['wpsg_page_agb']);
			if (isset($_REQUEST['wpsg_page_widerrufsbelehrung'])) $this->createPage(__('Widerrufsbelehrung', 'wpsg'), 'wpsg_page_widerrufsbelehrung', $_REQUEST['wpsg_page_widerrufsbelehrung']);
			if (isset($_REQUEST['wpsg_page_datenschutz'])) $this->createPage(__('Datenschutzerklärung', 'wpsg'), 'wpsg_page_datenschutz', $_REQUEST['wpsg_page_datenschutz']);
			if (isset($_REQUEST['wpsg_page_impressum'])) $this->createPage(__('Impressum', 'wpsg'), 'wpsg_page_impressum', $_REQUEST['wpsg_page_impressum']);
			if (isset($_REQUEST['wpsg_page_widerrufsbelehrung'])) $this->createPage(__('Widerrufsbelehrung', 'wpsg'), 'wpsg_page_widerrufsbelehrung', $_REQUEST['wpsg_page_widerrufsbelehrung']); 
			
			if ($_REQUEST['wphb_vk'] == '1') $this->setPage('wphb_vk', __('Versandkosten', 'wphb'), '12766C58F26', $this->get_option('wpsg_page_versand'));
			if ($_REQUEST['wphb_agb'] == '1') $this->setPage('wphb_agb', __('Allgemeine Geschäftsbedingungen', 'wphb'), '12766C46A8A', $this->get_option('wpsg_page_agb'));
			if ($_REQUEST['wphb_widerruf'] == '1') $this->setPage('wphb_widerruf', __('Widerrufsbelehrung', 'wphb'), '12766C53647', $this->get_option('wpsg_page_widerrufsbelehrung'));
			if ($_REQUEST['wphb_ds'] == '1') $this->setPage('wphb_ds', __('Datenschutzerklärung', 'wphb'), '12766C5E204', $this->get_option('wpsg_page_datenschutz'));
			if ($_REQUEST['wphb_imp'] == '1') $this->setPage('wphb_imp', __('Impressum', 'wphb'), '1293C20B491', $this->get_option('wpsg_page_impressum'));
			if ($_REQUEST['wphb_rueck'] == '1') $this->setPage('wphb_rueck', __('Rückgabebelehrung', 'wphb'), '12766C4A6BE', $this->get_option('wpsg_page_widerrufsbelehrung'));
			if ($_REQUEST['wphb_battery'] == '1') $this->setPage('wphb_battery', __('Batteriehinweise', 'wphb'), '134CBB4D101', $this->get_option('wpsg_page_battery'));
						
			if (!isset($_REQUEST['wpsg_mod_legaltexts_submitform']))
			{
			
				$this->addBackendMessage(__('Einstellungen gespeichert.', 'wphb'));
				
				header('Location: '.WPHB_URL_WP.'wp-admin/options-general.php?page=wpshopgermany-hb-Admin');
				die();

			}
				
		} // public function saveForm()
		
		public function showForm()
		{
			
			$this->view = array();
			$this->view['vk'] = $this->get_option('wphb_vk');
			$this->view['agb'] = $this->get_option('wphb_agb');
			$this->view['widerruf'] = $this->get_option('wphb_widerruf');
			$this->view['ds'] = $this->get_option('wphb_ds');
			$this->view['imp'] = $this->get_option('wphb_imp');
			$this->view['rueck'] = $this->get_option('wphb_rueck');
			$this->view['battery'] = $this->get_option('wphb_battery');
			
			$pages = get_pages();
			
			$arPages = array(
					'-1' => __('Neu anlegen und zuordnen', 'wphb')
			);
			
			foreach ($pages as $k => $v)
			{
				$arPages[$v->ID] = $v->post_title.' (ID:'.$v->ID.')';
			}
			
			$this->view['arPages'] = $arPages;
			
			return $this->render('views/form.phtml');
			
		} // public function showForm()
		
		public function admin_menu()
		{
			
			add_submenu_page('options-general.php', 'wpShopGermany - Händlerbund', 'Händlerbund', 'administrator', 'wpshopgermany-hb-Admin', array($this, 'dispatch'));
			
		} // public function admin_menu()
		
		/**
		 * Aktualsiert den Inhalt einer Seite mit den Daten, die vom Händlerbund kommen
		 * @param String $key Key für den Timestamp in der Datenbank
		 * @param String $title Titel der Seite um die es geht
		 * @param int $did DokumentenID
		 * @param int $page_id ID Der Seite
		 */
		private function setPage($key, $title, $did, $page_id)
		{
		
			global $wpdb, $current_user;
		
			$accessToken = $this->get_option('wphb_accessToken');
		
			if (!wphb_isSizedString($accessToken)) {
				$this->addBackendError(__('Bitte einen AccessToken angeben.', 'wphb')); return;
			}
		
			if (!wphb_isSizedInt($page_id)) {
				$this->addBackendError(wphb_translate(__('Seite für #1# nicht definiert.', 'wphb'), $title)); return;
			}
		
			$url = 'https://www.hb-intern.de/www/hbm/api/live_rechtstexte.htm?APIkey=1IqJF0ap6GdDNF7HKzhFyciibdml8t4v&did='.$did.'&AccessToken='.$accessToken;
		
			$content = $this->get_url_content($url);
		
			if ($content == 'DOCUMENT_NOT_AVAILABLE')
			{
		
				$this->addBackendError(wphb_translate(__('Die angeforderte Text-Id für #1# ist nicht verfügbar / falsch.', 'wphb'), $title));
				return;
		
			}
			else if ($content == 'SHOP_NOT_FOUND')
			{
		
				$this->addBackendError(wphb_translate(__('Die gegebene Shop-Id ist nicht bekannt.', 'wphb')));
				return;
		
			}
			else if ($content == 'WRONG_API_KEY')
			{
		
				$this->addBackendError(wphb_translate(__('Der AccessToken ist nicht korrekt angegeben.', 'wphb')));
				return;
		
			}
			else if (!wphb_isSizedString($content))
			{
		
				$this->addBackendError(wphb_translate(__('Keine Rückgabe für #1#.', 'wphb'), $title));
				return;
		
			}
			else
			{
		
				$page_id_db = $this->db->fetchOne("SELECT `ID` FROM `".$this->prefix."posts` WHERE `ID` = '".wphb_q($page_id)."'");
		
				if ($page_id_db != $page_id)
				{
		
					$this->addBackendError(wphb_translate(__('Die gesetzte Seite für #1# existiert nicht.', 'wphb'), $title));
					return;
		
				}
				else
				{
		
					$this->addBackendMessage(wphb_translate(__('Text für #1# wurde erfolgreich gesetzt.', 'wphb'), $title));
		
					$this->db->UpdateQuery($this->prefix."posts", array(
						"post_content" => wphb_q($content)
					), "`ID` = '".wphb_q($page_id_db)."'");
		
					$this->update_option($key, time());
		
				}
		
			}
		
		} // private function setPage($did, $page_id)
		
		public function get_option($key)
		{
			
			return get_option($key);
			
		}
		
		public function update_option($key, $value)
		{
			
			update_option($key, $value);
			
		}
				
		public function render($template)
		{
			
			ob_start();
			
			include dirname(__FILE__).'/../'.$template;
			
			$content = ob_get_contents(); ob_end_clean(); return $content;
						
		} // public function render($template)
		
		/**
		 * Fügt eine Hinweismeldung eines Backend Moduls hinzu
		 * Wird mittels writeBackendMessage ausgegeben
		 */
		public function addBackendMessage($message)
		{
		
			if (isset($_REQUEST['wpsg_mod_legaltexts_submitform'])) $GLOBALS['wpsg_sc']->addBackendMessage($message);
			
			if (!in_array($message, (array)$_SESSION['wphb']['backendMessage'])) $_SESSION['wphb']['backendMessage'][] = $message;
		
		} // public function addBackendMessage($message)
		
		/**
		 * Fügt eine neue Fehlermeldung eines Backend Moduls hinzu
		 */
		public function addBackendError($message)
		{
		
			if (isset($_REQUEST['wpsg_mod_legaltexts_submitform'])) $GLOBALS['wpsg_sc']->addBackendError($message);
			
			if (!in_array($message, (array)$_SESSION['wphb']['backendError'])) $_SESSION['wphb']['backendError'][] = $message;
		
		} // public function addBackendError($message)
		
		public function writeBackendMessage()
		{
		
			$strOut  = '';
		
			if (!isset($_SESSION['wphb']['backendMessage']) && !isset($_SESSION['wphb']['backendError'])) return;
		
			if (is_array($_SESSION['wphb']['backendMessage']) && sizeof($_SESSION['wphb']['backendMessage']) > 0)
			{
		
				$strOut  .= '<div id="wphb_message" class="updated">';
		
				foreach ($_SESSION['wphb']['backendMessage'] as $m)
				{
		
					$strOut .= '<p>'.$m.'</p>';
					
				}
		
				$strOut .= '</div>';
		
				unset($_SESSION['wphb']['backendMessage']);
		
			}
		
			if (wphb_isSizedArray($_SESSION['wphb']['backendError']))
			{
		
				$strOut  .= '<div id="wphb_message" class="error">';
		
				foreach ($_SESSION['wphb']['backendError'] as $m)
				{
		
					$strOut .= '<p>'.$m.'</p>';
		
				}
		
				$strOut .= '</div>';
		
				unset($_SESSION['wphb']['backendError']);
		
			}
		
			return $strOut;
		
		} // public function writeBackendMessage()
		
		/**
		 * Gibt die Antwort einer URL zurück
		 */
		public function get_url_content($url)
		{
		
			$Return = @file_get_contents($url);
		
			if (!$Return)
			{
		
				$ch = curl_init();
		
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_URL, $url);
		
				$data = curl_exec($ch);
				curl_close($ch);
		
				return $data;
		
			}
			else
			{
		
				return $Return;
		
			}
		
		} // public function get_url_content($url)
		
		/**
		 * Erstellt eine neue Seite im Wordpress
		 */
		public function createPage($title, $page_key, $page_id)
		{
		
			global $wpdb, $current_user;
		
			if ($page_id == -1)
			{
		
				$user_id = 0;
		
				if (function_exists("get_currentuserinfo"))
				{
					get_currentuserinfo();
					$user_id = $current_user->user_ID;
				}
		
				if ($user_id == 0 && function_exists("get_current_user_id"))
				{
					$user_id = get_current_user_id();
				}
		
				$page_id = $this->db->ImportQuery($this->prefix."posts", array(
						"post_author" => $user_id,
						"post_date" => "NOW()",
						"post_title" => $title,
						"post_date_gmt" => "NOW()",
						"post_name" => strtolower($title),
						"post_status" => "publish",
						"comment_status" => "closed",
						"ping_status" => "neue-seite",
						"post_type" => "page",
						"post_content" => '',
						"ping_status" => "closed",
						"comment_status" => "closed",
						"post_excerpt" => "",
						"to_ping" => "",
						"pinged" => "",
						"post_content_filtered" => ""
				));
		
				$this->db->UpdateQuery($this->prefix."posts", array(
						"post_name" => $this->clear($title, $page_id)
				), "`ID` = '".wphb_q($page_id)."'");
		
			}
		
			if ($page_id > 0)
			{
		
				$this->update_option($page_key, $page_id);
		
			}
		
		} // private function createPage($title)
		
		
		/**
		 * Bereinigt den URL Key bzw. das Path Segment
		 * Ist der Parameter post_id angegeben, so wird überprüft das kein Post ungleich dieser ID mit diesem Segment existiert
		 */
		public function clear($value, $post_id = false)
		{
		
			$arReplace = array(
					'/Ö/' => 'Oe', '/ö/' => 'oe',
					'/Ü/' => 'Ue', '/ü/' => 'ue',
					'/Ä/' => 'Ae', '/ä/' => 'ae',
					'/ß/' => 'ss', '/\040/' => '_',
					'/\€/' => 'EURO',
					'/\//' => '_',
					'/\[/' => '',
					'/\]/' => '',
					'/\|/' => ''
			);
		
			$strReturn = preg_replace(array_keys($arReplace), array_values($arReplace), $value);
			$strReturn = sanitize_title($strReturn);
		
			if (is_numeric($post_id) && $post_id > 0)
			{
		
				$n = 0;
		
				while (true)
				{
		
					$n ++;
		
					$nPostsSame = $this->db->fetchOne("SELECT COUNT(*) FROM `".$this->prefix."posts` WHERE `post_name` = '".wphb_q($strReturn)."' AND `id` != '".wphb_q($post_id)."'");
		
					if ($nPostsSame > 0)
					{
		
						$strReturn .= $n;
		
					}
					else
					{
		
						break;
		
					}
		
				}
		
			}
		
			return $strReturn;
		
		} // private function clear($value)
		
	} // public function admin_menu()

?>