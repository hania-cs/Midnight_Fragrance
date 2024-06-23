<?php
declare(strict_types=1);
namespace MaxButtons;

class supportController extends MaxController
{
  protected $view_template = 'maxbuttons-support';

  public function view()
  {
    $this->loadView();
    parent::view();
  }

  protected function loadView()
  {
     $this->view->browser = $this->maxbuttons_get_browser();
     $this->view->theme = \wp_get_theme();
     $this->view->plugins = get_plugins();
     $this->view->active_plugins = get_option('active_plugins', array());
  }

  public function handlePost()
  {
    return true; // no posts here.
  }

  // http://www.php.net/manual/en/function.get-browser.php#101125.
  // Cleaned up a bit, but overall it's the same.
  protected function maxbuttons_get_browser() {
      $user_agent = $_SERVER['HTTP_USER_AGENT'];
      $browser_name = 'Unknown';
      $platform = 'Unknown';
      $version= "";

      // First get the platform
      if (preg_match('/linux/i', $user_agent)) {
          $platform = 'Linux';
      }
      elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
          $platform = 'Mac';
      }
      elseif (preg_match('/windows|win32/i', $user_agent)) {
          $platform = 'Windows';
      }

      // Next get the name of the user agent yes seperately and for good reason
      if (preg_match('/MSIE/i', $user_agent) && !preg_match('/Opera/i', $user_agent)) {
  		$browser_name = 'Internet Explorer';
          $browser_name_short = "MSIE";
      }
      elseif (preg_match('/Firefox/i', $user_agent)) {
          $browser_name = 'Mozilla Firefox';
          $browser_name_short = "Firefox";
      }
      elseif (preg_match('/Chrome/i', $user_agent)) {
          $browser_name = 'Google Chrome';
          $browser_name_short = "Chrome";
      }
      elseif (preg_match('/Safari/i', $user_agent)) {
          $browser_name = 'Apple Safari';
          $browser_name_short = "Safari";
      }
      elseif (preg_match('/Opera/i', $user_agent)) {
          $browser_name = 'Opera';
          $browser_name_short = "Opera";
      }
      elseif (preg_match('/Netscape/i', $user_agent)) {
          $browser_name = 'Netscape';
          $browser_name_short = "Netscape";
      }

      // Finally get the correct version number
      $known = array('Version', $browser_name_short, 'other');
      $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
      if (!preg_match_all($pattern, $user_agent, $matches)) {
          // We have no matching number just continue
      }

      // See how many we have
      $i = count($matches['browser']);
      if ($i != 1) {
          // We will have two since we are not using 'other' argument yet
          // See if version is before or after the name
          if (strripos($user_agent, "Version") < strripos($user_agent, $browser_name_short)){
              $version= $matches['version'][0];
          }
          else {
              $version= $matches['version'][1];
          }
      }
      else {
          $version= $matches['version'][0];
      }

      // Check if we have a number
      if ($version == null || $version == "") { $version = "?"; }

      return array(
          'user_agent' => $user_agent,
          'name' => $browser_name,
          'version' => $version,
          'platform' => $platform,
          'pattern' => $pattern
      );
  }

} // controller.
