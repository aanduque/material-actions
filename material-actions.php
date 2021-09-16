<?php

/*
  Plugin Name: Material Actions
  Plugin URI: http://codecanyon.net/item/wp-admin-menu-manager/9520160
  Description: Navigate the WordPress admin interface was never that beautiful and simple. Easily customize the links you use the most to be always on your side. This is also the perfect add-on to our other plugin, <strong>Material WP</strong>.
  Version: 0.0.3
 */

/**
 * Loads our incredibily awesome Paradox Framework, which we are going to use a lot.
 */
require 'paradox/paradox-plugin.php';

/**
 * Our plugin starts here
 *
 * MaterialAdmin is a WordPress plugin that completly transforms your WordPress admin interface, giving it a 
 * awesome and beautful Google Material Design interface.
 */
class materialActions extends ParadoxPlugin {
  
  /**
   * @property int $fieldKey Our admin field id.
   */
  public $fieldKey  = 'field_54fcf81dba1f0';
  public $activated = 'display-admin-actions';
  public $menu;
  public $submenu;
  
  const MENU      = 'material-actions-menu';
  const SUBMENU   = 'material-actions-submenu';
  
  /**
   * Creates or returns an instance of this class.
   * @return object The instance of this class, to be used.
   */
  public static function init() {
    // If an instance hasn't been created and set to $instance create an instance and set it to $instance.
    if (null == self::$instance) {self::$instance = new self;}
    return self::$instance;
  }
  
  /**
   * Initializes the plugin adding all important hooks and generating important instances of our framework.
   */
  public function __construct() {
    
    // Setup
    $this->id         = 'material-actions';
    $this->textDomain = 'material-actions';
    $this->file       = __FILE__;
    
    // Set Debug Temporarily to True
    // $this->debug = true;
    
    // Calling parent construct
    parent::__construct();
    
    // Now we call the Advanced Custom Posts Plugin, that will handle our Options Page
    $this->addACF();
    
  }
  
  /**
   * Loads our ACF custom fields
   */
  public function acfAddFields() {
    require $this->path('inc/advanced-custom-fields-font-awesome/acf-font-awesome-v5.php');
  }
  
  /**
   * Load Our ACF Options
   */
  public function loadAcfOptions() {
    require $this->path('inc/material-admin-fields.php');
  }

  /**
   * Enqueue and register Admin JavaScript files here.
   */
  public function enqueueAdminScripts() {
    // Common and Admin JS
    wp_enqueue_script($this->id.'common', $this->url('assets/js/common.min.js'), false, '', true);
    wp_enqueue_script($this->id.'admin', $this->url('assets/js/admin.min.js'), array($this->id.'common'), '', true);
  }

  /**
   * Enqueue and register Admin CSS files here.
   */
  public function enqueueAdminStyles() {
    // Common and Admin styles
    wp_enqueue_style($this->id.'admin', $this->url('assets/css/admin.min.css'));
  }
  
  /**
   * Enqueue and register Frontend CSS files here.
   */
  public function enqueueFrontendStyles() {
    // Common and Admin styles
    // wp_enqueue_style($this->id.'frontend', $this->url('assets/css/admin.min.css'));
  }
  
  /**
   * Enqueue and register Admin JavaScript files here.
   */
  public function enqueueFrontendScripts() {
    // Common and Admin JS
    // wp_enqueue_script($this->id.'common', $this->url('assets/js/common.min.js'), false, '', true);
    // wp_enqueue_script($this->id.'admin', $this->url('assets/js/admin.min.js'), array($this->id.'common'), '', true);
  }
  
  /**
   * Here is where we create and manage our admin pages
   */
  public function adminPages() {}
  
  /**
   * Place code that will be run on first activation
   */
  public function onActivation() {
    $user = wp_get_current_user();
    $this->addDefaultActions($user->ID);
  }
  
  /**
   * After ACF saves
   * @param mixed $post_id The post being save or, in our case, the option.
   */
  public function onSave($post_id) {
    if ($post_id === 'options') {}
  }
  
  /**
   * Place code for your plugin's functionality here.  
   */
  public function Plugin() {
    
    // adds body class to our admin pages
    add_filter('admin_body_class', array($this, 'bodyClass'));
    
    // Footer Clean
    add_filter('update_footer', array(&$this, 'footerClean'), 11);
    
    // Check if this user has menus
    add_action('init', array(&$this, 'checkForActions'));
    
    // Save menus to posterior use.
    add_action('admin_menu', array($this, 'saveMenus'), 999999999999999999);
    
    // Populate Select
    add_filter('acf/load_field/name=internal-link', array(&$this, 'getInternalLinks'));
    
    // Check if this user has menus
    $this->loadAcfOptions();

  }
  
  /**
   * Adds custom body class, based on our theme
   */
  public function bodyClass($classes) {
    return $classes.$this->id;
  }
  
  /**
   * Adds Plus button
   */
  public function addPlusButton() {
    // Render our view
    $this->render('add-button');
  }
  
  /**
   * Clean up our footer texts
   */
  public function footerClean() {
    return '';
  }
  
  /**
   * Check if has actions
   */
  public function checkForActions() {
    
    // Get User
    $user = wp_get_current_user();
    
    // Get Actions
    $actions = get_field($this->id, "user_$user->ID");
    
    // Check if user has actions
    if ($actions === null || $actions === false) $this->addDefaultActions($user->ID);
    
    // Get where to display
    $admin = get_field($this->activated, "user_$user->ID");
    //$frontend = get_field('display-frontend', "user_$user->ID");
    
    // Adds our Plus Button on admin
    if ($admin) add_action('in_admin_header', array(&$this, 'addPlusButton'));
    
  }
  
  /**
   * Search Array Recursivily
   */
  public function searchArray($needle,$haystack) {
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if($needle===$value OR (is_array($value) && $this->searchArray($needle,$value) !== false)) {
            return $value;
        }
    }
    return false;
  }
  
  /**
   * Get menus for posterity
   */
  public function saveMenus() {

    // Check for previously saved menu
    $menu    = get_option(self::MENU);
    $submenu = get_option(self::SUBMENU);
    
    // Populate, if needed
    if ($menu === false || $submenu === false) {
      update_option(self::MENU, $GLOBALS['menu']);
      update_option(self::SUBMENU, $GLOBALS['submenu']);
    } else {
      // Compare to see if a resave is needed
      if ($menu !== $GLOBALS['menu'] || $submenu !== $GLOBALS['submenu']) {
        update_option(self::MENU, $GLOBALS['menu']);
        update_option(self::SUBMENU, $GLOBALS['submenu']);
      }
    }
  }
  
  /**
   * Get Internal Links choice
   */
  public function getInternalLinks($field) {
    
    $choices  = array();
    $menu     = get_option(self::MENU);
    $submenus = get_option(self::SUBMENU);
    
    // Prevent first run bug
    if (!is_array($menu) || !is_array($submenus)) return $field;
    
    foreach ($submenus as $menuName => $submenu) {
      
      // Add title
      $title = $this->searchArray($menuName, $menu);
      //var_dump($menu);
      $title = preg_replace('/[0-9]+/', '', $title[0]);
      
      // If parent does not exists, skip
      if (!empty($title)) {
        foreach ($submenu as $submenu) {
          $choices[$submenu[2]] = "$title: ". preg_replace('/[0-9]+/', '', wp_strip_all_tags($submenu[0], true));
        }
      }
      
    }
    
    $field['choices'] = $choices;
    return $field;
  }
  
  /**
   * New User or user without menus, add default ones.
   * @param int $userID The id of the user.
   */
  public function addDefaultActions($userID) {

    // Set display to true
    update_field($this->activated, true, "user_$userID");
    
    // Create our default menus
    $actions = array();
    
    // Action Visit website
    $actions[] = array(
      'action-name'   => __('Visit Website', $this->textDomain),
      'action-type'   => 'external-link',
      'external-link' => home_url(),
      'action-icon'   => 'fa-home',
      'action_target' => '_blank',
    );
    
    // New Post
    $actions[] = array(
      'action-name'   => __('Add Post', $this->textDomain),
      'action-type'   => 'internal-link',
      'internal-link' => 'post-new.php',
      'action-icon'   => 'fa-pencil',
    );
    
    // New Post
    $actions[] = array(
      'action-name'   => __('Add Page', $this->textDomain),
      'action-type'   => 'internal-link',
      'internal-link' => 'post-new.php?post_type=page',
      'action-icon'   => 'fa-file',
    );
    
    // Action edit Menu
    $actions[] = array(
      'action-name'   => __('Edit this menu', $this->textDomain),
      'action-type'   => 'external-link',
      'external-link' => admin_url('profile.php#material-actions'),
      'action-icon'   => 'fa-cog',
    );
    
    // Update
    $status = update_field($this->fieldKey, $actions, "user_$userID");
    //var_dump($status);
  }
  
}

/**
 * Finally we get to run our plugin.
 */
$MaterialActions = MaterialActions::init();