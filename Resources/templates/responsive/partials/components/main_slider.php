<div class="section main-slider">

    <?= $this->nav ? $this->insertif($this->nav) : '' ?>

	<?php if($this->banners): ?>

		<div class="slider slider-main">
			<?php foreach($this->banners as $banner): ?>
				<?php $banner_image=$banner->header_image ? $banner->header_image : $banner->image; ?>
					
				<?php if($banner_image): ?>
					<div class="item">
						<div class="image">
							<img src="<?= $banner_image->getLink(1920, 600, true) ?>" class="display-none-important img-responsive  hidden-xs visible-up-1400">
		                    <img src="<?= $banner_image->getLink(1400, 500, true) ?>" class="display-none-important img-responsive  hidden-xs visible-1051-1400">
			                <img src="<?= $banner_image->getLink(1051, 460, true) ?>" class="display-none-important img-responsive  hidden-xs visible-768-1050">
		                    <img src="<?= $banner_image->getLink(750, 600, true) ?>" class="img-responsive visible-xs">
						</div>
						<div class="main-info">
							<div class="container">
								<div class="row">
									<div class="col-lg-6 col-md-7 col-md-offset-1 col-sm-9 col-xs-8">
										<div>
											<span class="title hidden-xs">
											<?= $banner->description ? $banner->title : $this->text($banner::getSection($banner->section)) ?>
											</span>
										</div>
										<div class="description">
											<?= $banner->description ? $banner->description : $banner->title ?>
										</div>
										<a href="<?= $banner->url ? $banner->url : '/blog/'.$banner->id ?>" class="btn btn-white scroller"><?= $this->button_text ?></a>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>

			<?php endforeach; ?>
		</div>

	<?php endif; ?>

</div>