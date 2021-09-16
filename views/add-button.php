<?php 

$color = array(
  'yellow darken-1',
  'green',
  'blue',
  'grey',
  'teal',
  'orange',
  'red',
  'yellow darken-2',
  'green darken-1',
  'blue darken-1',
  'grey darken-1',
  'teal darken-1',
  'orange darken-1',
  'red darken-1',
); 

$user = wp_get_current_user();

?>

<div id="mvp-plus-button-container" class="fixed-action-btn">
  <ul>
    
    <?php 
      $i = 0; 
      if (have_rows($this->id, "user_$user->ID")) : 
        while (have_rows($this->id, "user_$user->ID")) : 
        the_row();

        // Check for custom bg color
        $bg = get_sub_field('action-bg');
        $bg = $bg ? "background-color: $bg !important;" : ''; 

        // Get the type of link and the link
        $linkType = get_sub_field('action-type');

        // Switch
        switch($linkType) {
          
          case 'internal-link':
            $link = get_sub_field('internal-link');
            $onclick = '';
            break;
          
          default:
            $link = get_sub_field('external-link');
            $onclick = '';
            break;
          
          case 'javascript':
            $link    = '#';
            $onclick = get_sub_field('javascript');
            break;
        }
    ?>
    
    <li>
      <a target="<?php the_sub_field('action-target'); ?>" onclick="<?php echo $onclick; ?>" href="<?php echo $link; ?>" data-position="left" data-delay="01" data-tooltip="<?php the_sub_field('action-name'); ?>" style="<?php echo $bg; ?>" class="tooltiped btn-floating waves-effect waves-light <?php echo $color[$i]; ?>">
      <i class="fa <?php the_sub_field('action-icon'); ?>"></i>
      </a>
    </li>
  
    <?php $i++; endwhile; endif; ?>
    
  </ul>
  
  <a id="material-admin-trigger" class="btn-floating btn-large waves-effect waves-light wp-ui-highlight">
    <i class="large mdi-content-add"></i>
  </a>
</div>