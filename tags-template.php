<?php
function product_detail_tag( $object ) {
    $tagsID = Tag::getsByObjectID($object->id, 'product');
    $tags   = Tag::gets(['where_in' =>['field' => 'id', 'data' => $tagsID]]);
	$str = '';
	if( have_posts($tags) ) {
		?>
		<div class="tags">
			<p>Tags:
				<?php
					foreach ($tags as $tag):
						$str .= '<a href="'.Url::permalink('san-pham?tag='.$tag->slug).'">'.$tag->name.'</a>';
					endforeach;
					echo $str;
				?>
			</p>
		</div>
		<style type="text/css">
			.tags { margin: 20px 0 0 0; }
			.tags a { color:#000; border:1px dashed #ccc; border-radius:4px; padding:3px 10px; font-size:13px; margin-right:5px; }
			.tags a:hover { color:#f15928; }
		</style>
		<?php
	}
}
add_action('product_detail_info', 'product_detail_tag', 50 );

function post_detail_tag($content) {

	if(Template::isPage('post_detail')) {

		$object = get_object_current();

		if(have_posts($object) && $object->post_type == 'post') {

			ob_start();

            $tagsID = Tag::getsByObjectID($object->id, 'post');

            $tags   = Tag::gets(['where_in' =>['field' => 'id', 'data' => $tagsID]]);

			$str = '';

			if(have_posts($tags)) {
				?>
				<div class="tags">
					<p>
						Tags:
						<?php
							foreach ($tags as $tag): $str .= '<a href="'.Url::permalink('tag/'.$tag->slug).'">'.$tag->name.'</a>'; endforeach;
							echo $str;
						?>
					</p>
				</div>
				<style type="text/css">
					.object-detail-content { overflow:hidden; }
					.tags { margin: 20px 0 0 0; }
					.tags a { color:#000; border:1px dashed #ccc; border-radius:4px; padding:3px 10px; font-size:13px; margin-right:5px; }
					.tags a:hover { color:#f15928; }
				</style>
				<?php
			}
			
			$content .= ob_get_contents();
			ob_clean();
			ob_end_flush();
		}
	}
	return $content;
}
add_filter('the_content', 'post_detail_tag', 50 );