

<div class='maxbutton-welcome-container'>

  <h3><?php _e('Welcome to MaxButtons!','maxbuttons'); ?></h3>

  <p class='started'><?php printf(__('To get you started, %s Create your first button %s' ,'maxbuttons'), '<a href="' . $this->getButtonLink(0, array('firstbutton' => true) ) . '" class="button-primary">', '</a>'); ?></a></p>


  <p><?php _e('Some links that may be helpful:','maxbuttons') ?></p>
  <ul>
    <li><a href="https://maxbuttons.com/create-wordpress-button/#button" target="_blank"><?php _e('Creating buttons with MaxButtons','maxbuttons'); ?></li>
    <li><a href="https://wordpress.org/support/plugin/maxbuttons" target="_blank"><?php _e('Support Forums','maxbuttons'); ?></a></li>
  </ul>


<section class='organize'>
  <h3><?php _e('Organize your Designs','maxbuttons'); ?></h3>

  <p><?php _e('MaxButtons uses shortcodes. These are small snippets you can copy into your posts and pages. You will see a button in your post editor which will make this easier.', 'maxbuttons') ?></p>

  <p><strong><?php _e('Do Not Repeat!','maxbuttons'); ?></strong> <?php _e('You can use your button design many times. Even with a different text and link!', 'maxbuttons'); ?></p>


  <p>[maxbutton id=1 url="http://example.com" text="Example Text"]</p>
</section>


</div>
