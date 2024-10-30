<?php   
	/*
		Plugin Name: Convertizer.fr
		Description: Avec convertizer, Cr&eacute;ez un lien avec vos clients
		Plugin URI: https://www.convertizer.fr/
		Version: 1.3.2
		Author: Bassem Rabia
		Author URI: mailto:bassem.rabia@gmail.com
		License: GPLv2
	*/
	
	function convertizer_add_async_attribute($tag, $handle){
		if('convertizer-async' !== $handle)
			return $tag;
		return str_replace('src', 'async="async" src', $tag);
	}	
	add_filter('script_loader_tag', 'convertizer_add_async_attribute', 10, 2);
		
	class Convertizer{
		public function __construct(){
			$this->signature = array(
				'pluginName' => 'Convertizer',
				'pluginNiceName' => 'Convertizer',
				'pluginSlug' => 'convertizerfr',
				'pluginVersion' => '1.3.2',
				'pluginRemoteURL' => 'https://www.convertizer.fr/',
				'pluginEnabled' => 0,
				'protocol' => stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0?'http://' : 'https://',
				'partnerHost' => str_replace('www.', '', $_SERVER['HTTP_HOST']),
				'partnerName' => preg_replace('/[^a-z]+/', '', str_replace('www.', '', $_SERVER['HTTP_HOST']))
			);
				
			add_action('wp_dashboard_setup', array(&$this, 'convertizer_dashboard')); 
			add_action('wp_enqueue_scripts', array(&$this, 'convertizer_enqueue'));
		
			add_action('admin_enqueue_scripts',array(&$this, 'convertizer_admin_enqueue'));
			add_action('admin_menu', array(&$this, 'convertizer_menu'));
		}
	
		public function convertizer_dashboard(){
			wp_add_dashboard_widget('dashboard_widget', $this->signature['pluginName'] .' '.$this->signature['pluginVersion'], 'dashboardFunction');
			function dashboardFunction($post, $callback_args){
				?>
				<ul class="WordPressLiveSupportDashboard">
					<li class="WordPressLiveSupportDashboardSettings">
						<a href="admin.php?page=convertizer-main-menu">
							<?php _e('Configure', 'convertizer');?>
						</a>
					</li> 
				</ul>
				<?php
			}
		}
		
		public function convertizer_menu(){
			add_menu_page(
				$this->signature['pluginNiceName'], 
				$this->signature['pluginNiceName'], 
				'manage_options', 
				strtolower($this->signature['pluginSlug']).'-main-menu', array(&$this, 'convertizer_page'), 
				plugins_url(strtolower($this->signature['pluginSlug']).'/images/16.png'),
				150
			);
		}
		
		public function convertizer_admin_enqueue(){
			wp_enqueue_style('convertizer-admin-style', plugins_url('css/admin.css', __FILE__));
		}	
		
		public function convertizer_remote($partnerName){
			$Remote = 'https://api.convertizer.fr/convertizerfr/log?taskId=createPartner'; 
			$api_params = array( 
				'partnerName'	=> $this->signature['partnerName'],
				'partnerHost'	=> $this->signature['partnerHost'],
				'admin_email'   => urlencode(get_option('admin_email'))
			);
			?>
			<script>
			jQuery.ajax({
				type: 'GET',
				url: '<?php echo add_query_arg($api_params, $Remote)?>',
				success: function(r){
					console.log(r);
				}
			});
			</script>
			<?php
		}
		
		public function convertizer_page(){
			?>
			<div class="wrap columns-2">
				<div id="<?php echo $this->signature['pluginSlug'];?>" class="icon32"></div>  
				<h2><?php echo $this->signature['pluginNiceName'] .' '.$this->signature['pluginVersion'];?></h2>
				<div class="<?php echo $this->signature['pluginSlug'];?>" id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						
						<div id="postbox-container-1" class="postbox-container">							
							<div class="postbox">
								<h3><span><?php _e('Need help', 'convertizer');?>?</span></h3>
								<div class="inside">
									<?php _e('You have a question, or need more information', 'convertizer');?>?
								</div>
							</div>
						</div> 
						
						<div id="postbox-container-2" class="postbox-container">
							<div class="page-header">
								<strong>
								<?php
								
								$pSignature = get_option($this->signature['pluginSlug']);
								// echo '<pre>';print_r($pSignature); echo '</pre>';
								if(isset($_GET['e'])){
									$pSignature['pluginEnabled'] = $_GET['e'];
									// echo '<pre>';print_r($pSignature); echo '</pre>';
									update_option($this->signature['pluginSlug'], $pSignature);
									if($_GET['e'] == 1){
										$this->convertizer_remote($this->signature['partnerName']);
									}
								}
								
								if(!isset($pSignature['pluginEnabled']) OR $pSignature['pluginEnabled'] == 0){
									?>
									<a style="text-decoration: none;float: right;padding: 3px 5px;" href="admin.php?page=convertizer-main-menu&e=1"><?php _e('Activate it now', 'convertizer');?></a>
									<?php echo $this->signature['pluginName'];?> <?php _e('is OFF', 'convertizer');?>
									<?php
								}else{
									?>
									<a style="text-decoration: none;float: right;padding: 3px 5px;" href="admin.php?page=convertizer-main-menu&e=0">| <?php _e('Desactivate it now', 'convertizer');?> </a>
									<a target="_blank" style="text-decoration: none;float: right;padding: 3px 5px;" href="http://<?php echo $this->signature['partnerName'];?>.dashboard.convertizer.fr/"><?php _e('Manage campaigns', 'convertizer');?></a>
									<?php echo $this->signature['pluginName'];?> <?php _e('is ON', 'convertizer');?>
									<?php
								}
								?>
								
							</strong>
							</div>
							<div id="<?php echo $this->signature['pluginNiceName'];?>_container">
								<div class="content">
									<table class="form-table" role="presentation">
										<tbody>
											<tr>
												<th scope="row">
													<label for="blogname">Token</label>
												</th>
												<td>
													<?php echo sha1($this->signature['partnerHost']);?>
												</td>
											</tr>
											<tr>
												<th scope="row">
													<label for="blogname"><?php _e('partnerName', 'convertizer'); ?></label>
												</th>
												<td>
													<?php echo $this->signature['partnerName'];?>
												</td>
											</tr>
											<tr>
												<th scope="row">
													<label for="blogname"><?php _e('partnerHost', 'convertizer'); ?></label>
												</th>
												<td>
													<?php echo $this->signature['partnerHost'];?>
												</td>
											</tr>
											<tr>
												<th scope="row">
													<label for="blogname"><?php _e('admin_email', 'convertizer'); ?></label>
												</th>
												<td>
													<?php echo get_option('admin_email');?>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		
		public function convertizer_enqueue(){
			$pSignature = get_option($this->signature['pluginSlug']);
			// echo 'pSignature = <pre>'; print_r($pSignature);
			if(isset($pSignature['pluginEnabled']) AND $pSignature['pluginEnabled'] == 1){
				wp_register_script('convertizer-async', $this->signature['protocol'].'api.convertizer.fr/'.$this->signature['partnerName'].'/partner.js?v='.rand(1,99).'&wp_enqueue_script', '', 2, false);
				wp_enqueue_script('convertizer-async');
			}
		}
	}
	
	new Convertizer();
?>