<?php
  /*
  Plugin Name: Bootstrap fullscreen slider
  Plugin URI:  https://github.com/jeffersoncarrenho/
  Description: Bootstrap fullscreen slider
  Version:     1
  Author:      Jefferson Lima
  Author URI:  https://github.com/jeffersoncarrenho
  License:     GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /languages
  */

  add_action('wp_enqueue_scripts', 'bootstrap_slider_load_styles');

  function bootstrap_slider_load_styles(){
    wp_register_style('custom-css', plugins_url('custom.css',__FILE__ ));
    wp_enqueue_style('custom-css');
  }

  add_action ('init', 'post_type_bootstrap_slider');

  function post_type_bootstrap_slider(){
    $labels = array(
      'name' => _x('Slides', 'post type general name'),
      'singular_name' => _x('Slide', 'post type singular name'),
      'add_new' => __('Adicionar Novo Slide'),
      'add_new_item' => __('Novo Slide'),
      'new_item' => __('Novo Slide'),
      'parent_item_colon' => '',
      'menu_name' => 'Home Slideshow'
    );

    $args = array(
      'labels' => $labels,
      'public' => true,
      'show_ui' => true,
      'menu_position' => 5,
      'register_meta_box_cb' => 'bootstrap_slider_meta_box',
      'menu_icon' => 'dashicons-images-alt2',
      'supports' => array( 'title', 'thumbnail', 'editor' )
    );

    register_post_type('bootstrap_slider', $args);
    flush_rewrite_rules();
  }

  function bootstrap_slider_meta_box(){
    add_meta_box('meta_box_slide_url', __('Link do Slide'), 'meta_box_slide_url', 'bootstrap_slider', 'normal', 'high');
    add_meta_box('meta_box_slide_type', __('Tipo do Slide'), 'meta_box_slide_type', 'bootstrap_slider', 'normal', 'high');
    add_meta_box('meta_box_slide_front_image', __('Imagem da frente do Slide'), 'meta_box_slide_front_image', 'bootstrap_slider', 'advanced', 'high');
  }

  function meta_box_slide_url(){
    global $post;
    $metaBoxUrl = get_post_meta($post->ID, 'slide_url', true);
    $metaCheck = get_post_meta($post->ID, 'meta_box_check', true);
    ?>
    <input type="text" name="slide_url" id="inputSlideUrl" style="font-size: 14px; height:34px; width:100%;" value="<?php echo $metaBoxUrl; ?>" />
    <label>Abrir em nova guia</label>
    <input type="checkbox" name="meta_box_check" id="meta_box_check" <?php echo ($metaCheck === 'on') ? 'checked' : ''; ?> />
    <?php
  }

  function meta_box_slide_type(){
    global $post;
    $metaBoxType = get_post_meta($post->ID, 'slide_type', true);
    ?>
    <input type="text" name="slide_type" id="inputSlideType" style="font-size: 14px; height:34px; width:100%;" value="<?php echo $metaBoxType; ?>" />
    <?php
  }

  function meta_box_slide_front_image(){
    global $post;
    $metaBoxImage = get_post_meta($post->ID, 'slide_front_image', true);
      // Carrega as bibliotecas javascript necessárias para usar os modais
      if ( ! did_action( 'wp_enqueue_media' ) ){
        wp_enqueue_media();
      }
      ?>
      <img src="<?php echo $metaBoxImage; ?>" width="250px" id="slider_image">
      <br />
      <input id="slide_front_image" name="slide_front_image" type="hidden" value="<?php echo $metaBoxImage; ?>"/>
      <button id="escolher" class="button button-primary button-small">Escolher imagem</button>
      <button id="remover" class="button button-small">Remover Imagem</button>
      <br />
      <script type="text/javascript">
        $('#remover').click(function(event){
          event.preventDefault();
          $('#slide_front_image').val('');
          $('#slider_image').attr("src","");
        });

        $('#escolher').click(function(){
          wp.media.editor.send.attachment = function(props, attachment){
            $('#slide_front_image').val(attachment.url);
            $('#slider_image').attr("src",attachment.url);
          }
          wp.media.editor.open(this);
          return false;
        });
      </script>

      <?php
    }

  //salva o novo slide
  add_action('save_post', 'save_bootstrap_slider');
  function save_bootstrap_slider(){
    global $post;
    update_post_meta($post->ID, 'slide_url', $_POST['slide_url']);
    update_post_meta($post->ID, 'meta_box_check', $_POST['meta_box_check']);
    update_post_meta($post->ID, 'slide_type', $_POST['slide_type']);
    update_post_meta($post->ID, 'slide_front_image', $_POST['slide_front_image']);
  }

  //cria o slideshow
  function bootstrap_slider() {
    $slidesArgs = array( 'post_type' => 'bootstrap_slider', 'posts_per_page' => -1, 'order' => 'DESC');
    $slidesLoop = new WP_Query( $slidesArgs );
    $count = $slidesLoop->post_count;
    ?>

    <div id="myCarousel" class="carousel slide carousel-fullscreen carousel-fade" data-ride="carousel">
      <!-- Indicators -->
      <ol class="carousel-indicators hidden-xs hidden-sm">
        <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
        <?php
        for($i = 1; $i<$count; $i++){
          ?>
          <li data-target="#myCarousel" data-slide-to="<?php echo $i; ?>"></li>
          <?php
        }
        ?>
      </ol>

      <div class="carousel-inner">
        <?php
        $contador = 0;
        while ($slidesLoop->have_posts() ) : $slidesLoop->the_post();
        ?>
        <div class="item <?php if($contador == 0) echo 'active'?>" style="background-image:url('<?php the_post_thumbnail_url();?>">
          <div class="carousel-caption">
            <?php if (get_post_meta( get_the_id(), 'slide_front_image', true)): ?>
              <div class="col-sm-4">
                <img src="<?php echo esc_url( get_post_meta( get_the_id(), 'slide_front_image', true) ); ?>" class="img-responsive">
              </div>
              <div class="col-sm-8">
              <?php else: ?>
                <div class="col-sm-12">
              <?php endif; ?>
              <h1 class="super-heading"><?php the_title(); ?></h1>
              <?php echo the_content();?>
              <?php if (get_post_meta( get_the_id(), 'slide_url', true)): ?>
                <a href="<?php echo esc_url( get_post_meta( get_the_id(), 'slide_url', true) ); ?>" class="btn btn-primary" target="<?php echo (get_post_meta( get_the_id(), 'meta_box_check', true) ==='on' ) ? '_blank': '_self' ; ?>"  >
                  <?php echo get_post_meta( get_the_id(), 'slide_type', true); ?>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php
        $contador++;
      endwhile;
      ?>
    </div>
    <!-- Controls -->
    <a class="left carousel-control" href="#myCarousel" data-slide="prev">
      <span class="icon-prev"></span>
    </a>
    <a class="right carousel-control" href="#myCarousel" data-slide="next">
      <span class="icon-next"></span>
    </a>
  </div>
  <!-- /.carousel -->
  <?php
}
?>
