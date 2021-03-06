<h2 id="page_title"><?php echo lang('blog:archive_title');?></h2>
<h3><?php echo $month_year;?></h3>
<?php if ( ! empty($blog)): ?>
	<?php foreach ($blog as $post): ?>
	<div class="blog_post">
		<!-- Post heading -->
		<div class="post_heading">
			<h2><?php echo  anchor('blog/' .date('Y/m', $post->created_on) .'/'. $post->slug, $post->title) ?></h2>
			<p class="post_date">
				<?php echo lang('blog:posted_label');?>: <?php echo format_date($post->created_on); ?>
			</p>

			<?php if ($post->category_slug): ?>
			<p class="post_category">
				<?php echo lang('blog:category_label');?> : <?php echo anchor('blog/category/'.$post->category_slug, $post->category_title);?>
			</p>
			<?php endif ?>

			<?php if ($post->keywords): ?>
			<p class="post_keywords">
				<?php echo lang('blog:tagged_label');?>:
				<?php echo $post->keywords ?>
			</p>
			<?php endif ?>

		</div>
		<div class="post_body">
			<?php echo $post->intro ?>
		</div>
	</div>
<?php endforeach ?>
<?php echo $pagination['links'] ?>
<?php else: ?>
<p><?php echo lang('blog:currently_no_posts');?></p>
<?php endif ?>
