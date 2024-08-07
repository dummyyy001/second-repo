<div class="asl-cont asl-card-style asl-<?php echo $card_layout; ?> <?php echo $slider_enabled ? 'asl-grid-slider' : ''; ?>">

    <div class="sl-container">
        <div class="pt-5"></div>
        <div class="asl-list-cont">
            <?php echo $slider_enabled ? '<div class="sviper sl_grid_slider"><div class="sviper-wrapper">' : '<div class="sl-row">'; ?>
                <?php foreach ($stores as $store) : ?>

                    <?php $class = $slider_enabled ? 'sviper-slide' : 'pol-lg-4 pol-md-6'; ?>
                    
                    <div class="<?php echo $class; ?>">
                        <?php include($card_partial_path); ?>
                    </div>
                <?php endforeach; ?>

            </div>

            <?php if ($slider_enabled) : ?>
                <div class="sviper-button-next">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 500 500" style="enable-background:new 0 0 500 500;" xml:space="preserve">
                    <path d="M76.3,236c-2.7,2.7-4.2,6.3-4.3,10.1c0,3.8,1.5,7.4,4.3,10.1l91.8,91.6c5.7,5.6,14.8,5.6,20.5,0s5.7-14.8,0-20.4l0,0
                            l-67.2-66.9h293.2c7.9,0,14.4-6.4,14.4-14.3s-6.4-14.4-14.4-14.4l0,0H121.4l67.2-67c5.7-5.6,5.7-14.8,0-20.4
                            c-5.7-5.6-14.8-5.6-20.5,0L76.3,236z"></path>
                    </svg>
                </div>
                <div class="sviper-button-prev">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 500 500" style="enable-background:new 0 0 500 500;" xml:space="preserve">
                    <path d="M76.3,236c-2.7,2.7-4.2,6.3-4.3,10.1c0,3.8,1.5,7.4,4.3,10.1l91.8,91.6c5.7,5.6,14.8,5.6,20.5,0s5.7-14.8,0-20.4l0,0
                            l-67.2-66.9h293.2c7.9,0,14.4-6.4,14.4-14.3s-6.4-14.4-14.4-14.4l0,0H121.4l67.2-67c5.7-5.6,5.7-14.8,0-20.4
                            c-5.7-5.6-14.8-5.6-20.5,0L76.3,236z"></path>
                    </svg>
                </div>
            <?php endif; ?>

            <?php echo $slider_enabled ? '</div></div>' : '</div>'; ?>
        </div>
    </div>
</div>