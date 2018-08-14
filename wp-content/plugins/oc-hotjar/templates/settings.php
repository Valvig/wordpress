<div class="wrap">
    <a href="https://www.hotjar.com">
      <img src="<?php echo plugins_url( '../images/hotjar-logo.png', __FILE__ ) ?>" style="display: block; margin: auto; width: 20%;">
    </a>

    <p>Hotjar is a new, powerful tool that reveals the online behavior and voice of your users. By combining both A) Analysis and B) Feedback tools, Hotjar gives you the ‘big picture’ of how to improve your site's user experience and performance/conversion rates.</p>

    <p>The Analysis tools allow you to measure and observe user behavior, see what users do, while the Feedback tools allow you to hear what your users have to say, the Voice of User.</p>

    <p>To use this plugin, you first need to set up a HotJar account. Once you have set up a site in HotJar you will be given a Site Identifier. Just supply this in the field below and you're good to go!</p> 

    <form method="post" action="options.php">
        <?php @settings_fields('hotjar-group'); ?>
        <?php @do_settings_fields('hotjar-group'); ?>

        <?php do_settings_sections('hotjar'); ?>

        <?php @submit_button(); ?>
    </form>
</div>
