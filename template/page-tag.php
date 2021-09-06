<?php
$tag = Url::segment(2);

if($tag == 'tag') { $tag = Url::segment(3); }

$tag = Tag::get(['where' => ['slug' => $tag]]);

$objects = [];

if(have_posts($tag)) {
    $listID = Tag::getsObject($tag->id, 'post');
    $objects = Posts::gets(array(
        'where_in' => array(
            'field' => 'id',
            'data'  => $listID
        )
    ));
}

if(have_posts($objects)) {

    $layout 		= get_theme_layout();

    $layout_setting = get_theme_layout_setting('post_category');

    $col = '';

    if( $layout_setting['style'] == 'horizontal') {

        $col = array();

        $col['lg'] = ( $layout_setting['horizontal']['category_row_count'] != 5) ? 12/$layout_setting['horizontal']['category_row_count'] : 15;

        $col['md'] = ( $layout_setting['horizontal']['category_row_count'] != 5) ? 12/$layout_setting['horizontal']['category_row_count'] : 15;

        $col['sm'] = ( $layout_setting['horizontal']['category_row_count_tablet'] != 5) ? 12/$layout_setting['horizontal']['category_row_count_tablet'] : 15;

        $col['xs'] = ( $layout_setting['horizontal']['category_row_count_mobile'] != 5) ? 12/$layout_setting['horizontal']['category_row_count_mobile'] : 15;

        $col = 'col-xs-'.$col['xs'].' col-sm-'.$col['sm'].' col-md-'.$col['md'].' col-lg-'.$col['lg'].'';
    }
    ?>
    <div class="post">
        <?php echo '<div class="row">';
        foreach ($objects as $key => $val):
            if($layout_setting['style'] == 'vertical')
                $this->template->render_include('loop/item_post',array('val' => $val));
            else {
                echo '<div class="col-md-'.$col.'">';
                $this->template->render_include('loop/item_post_horizontal',array('val' => $val));
                echo '</div>';
            }
        endforeach; echo '</div>' ?>
        <nav class="text-center">
            <?= (isset($pagination))?$pagination->html():'';?>
        </nav>
    </div>
<?php } ?>