<div class="updated">
<p><?php echo GTP_NAME, __('Requirements Alert: Please meet requirements listed below to use Zanto.','Zanto')?></p>
<ul class="ul-disc">
<?php
$zwt_unfullfilled_requirments = zwt_requirements_missing(); 
foreach($zwt_unfullfilled_requirments as $req=>$status){
        if(0==$status){
			if('Multisite'==$req){
            ?>
	          <li><?php _e('<strong>Wordpress Multisite</strong> has not been activated for your installation. to use the Zanto Wordpress Translation Plugin, you 
			  need to activate the Multisite mode for Wordpress. You can learn how to do it
			  <strong> <a href="http://zanto.org/?p=46">here</a>','Zanto')?></strong>
			 </li>
            <?php
			 }
		    if('zwt_PHP_VERSION'==$req){
			?>
	         <li><strong>PHP <?php echo GTP_REQUIRED_PHP_VERSION, __('</strong> <em>(You\'re running version <?php echo PHP_VERSION; ?>)</em>','Zanto')?></li>
			<?php
			 }
			if ('zwt_WP_VERSION'==$req){
			?>
	          <li><?php _e('You are running an old version of WordPress please upgrade your WordPress Installation to use Zanto','Zanto') ?></li>
			<?php
			 }
			
		}
 }
 ?>
 </ul>
</div>