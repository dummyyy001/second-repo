<?php
/**
 * Single job template
 */

get_header();

$ad_id = get_the_ID();
?>
<div id="main-content" class="main-container blog-single job-ad-single"  role="main">
    <div class="container">
        <div class="job-ad-overview row mx-1 mx-md-0">
            <div class="col-12">
                <hr class="w-100 mw-100 bg-transparent h-0 border border-primary" style="height: 0px;">
                <div class="row">
                    <div class="col-12 col-md-8">
                        <span class="align-items-md-center">
                            <p class="job-ad-section-title fw-semibold font-weight-bold mb-0 d-block d-md-inline-block"><?php _e( 'Position', 'bh2ojaa' ); ?>: </p>
                            <h1 class="mb-0 d-block d-md-inline-block"><?php the_title(); ?></h1>
                        </span>
                        <?php
                        if ( $location = bh2ojaa_get_job_ad_portal_field( $ad_id, 'Location' ) ) {
                            ?>
                            <p class="fw-semibold font-weight-bold mb-0 mt-3">
                                <span class="job-ad-section-title d-block d-md-inline-block"><?php _e( 'Location', 'bh2ojaa' ); ?>:</span>
                                <span class="fw-light font-weight-light"><?php echo $location; ?></span>
                            </p>
                            <?php
                        }

                        if ( $work_type = bh2ojaa_get_job_ad_portal_field( $ad_id, 'Work Type' ) ) {
                            ?>
                            <p class="fw-semibold font-weight-bold mb-0 mt-3">
                                <span class="job-ad-section-title d-block d-md-inline-block"><?php _e( 'Work Type', 'bh2ojaa' ); ?>:</span>
                                <span class="fw-light font-weight-light"><?php echo $work_type; ?></span>
                            </p>
                            <?php
                        }

                        if ( $dpa_status = bh2ojaa_get_job_ad_portal_field( $ad_id, 'DPA Status' ) ) {
                            ?>
                            <p class="fw-semibold font-weight-bold mb-0 mt-3">
                                <span class="job-ad-section-title d-block d-md-inline-block"><?php _e( 'DPA Status', 'bh2ojaa' ); ?>:</span>
                                <span class="fw-light font-weight-light"><?php echo $dpa_status; ?></span>
                            </p>
                            <?php
                        }

                        if ( $mmn = bh2ojaa_get_job_ad_portal_field( $ad_id, 'Modified Monash Number' ) ) {
                            ?>
                            <p class="fw-semibold font-weight-bold mb-0 mt-3">
                                <span class="job-ad-section-title d-block d-md-inline-block"><?php _e( 'Modified Monash Number', 'bh2ojaa' ); ?>:</span>
                                <span class="fw-light font-weight-light"><?php echo $mmn; ?></span>
                            </p>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="col-12 mt-3 d-md-flex align-items-center justify-content-md-end col-md-4">
                        <a class="btn btn-primary btn-lg fw-bold font-weight-bold" href="#job-apply-form-wrapper"><?php _e( 'Apply Now', 'bh2ojaa' ); ?></a>
                    </div>
                </div>
                <hr class="w-100 mw-100 bg-transparent h-0 border border-primary" style="height: 0px;">
                <section class="wp-block-job-overview mt-4">
                    <div class="container">
                        <div class="row mb-2">
                            <div class="col-12 col-md-6 pe-xl-4 mb-4 mb-md-0">
                                <?php the_content(); ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?php
                                $thumbnail_src  = bh2ojaa_get_job_ad_portal_field( $ad_id, 'Image URL' );

                                if ( $thumbnail_src ) {
                                    ?>
                                    <div class="job-ad-thumbnail mb-3">
                                        <img src="<?php echo $thumbnail_src; ?>" alt="<?php echo get_the_title( $ad_id ); ?>">
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="job-ad-classification">
                                    <h2><?php _e( 'Job Classification', 'bh2ojaa' ); ?></h2>
                                    <?php
                                    if ( $cat = bh2ojaa_get_job_ad_classification( $ad_id, 'Category' ) ) {
                                        ?>
                                        <p class="fw-semibold font-weight-bold mb-0 mt-3">
                                            <span class="job-ad-section-title d-block d-md-inline-block fw-bold font-weight-bold"><?php _e( 'Category', 'bh2ojaa' ); ?>:</span>
                                            <span class="fw-light font-weight-light"><?php echo $cat; ?></span>
                                        </p>
                                        <?php
                                    }

                                    if ( $subcat = bh2ojaa_get_job_ad_classification( $ad_id, 'Sub Category' ) ) {
                                        ?>
                                        <p class="fw-semibold font-weight-bold mb-0 mt-3">
                                            <span class="job-ad-section-title d-block d-md-inline-block fw-bold font-weight-bold"><?php _e( 'Sub Category', 'bh2ojaa' ); ?>:</span>
                                            <span class="fw-light font-weight-light"><?php echo $subcat; ?></span>
                                        </p>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="job-apply-form-wrapper" class="job-apply-form-wrapper">
                    <div class="container">
                        <div class="row justify-content-xl-center">
                            <div class="col-12 text-center col-xl-9 col-xxl-8 col-3xl-7">
                                <h2><?php _e( 'Please complete the form below to apply for this position', 'bh2ojaa' ); ?></h2>
                            </div>
                        </div>
                        <div class="row mt-xl-4">
                            <div class="col-12 col-md-6 d-flex flex-column-reverse justify-content-md-center ">
                                <div class="title-wrapper">
                                    <h3><?php echo __( 'Consult with us at', 'bh2ojaa' ) . ' ' . get_bloginfo( 'name' ); ?></h3>
                                </div>
                                <?php
                                if ( has_custom_logo() ) {
                                    $custom_logo_id = get_theme_mod( 'custom_logo' );
                                    $image          = wp_get_attachment_image_src( $custom_logo_id , 'full' );
                                    ?>
                                    <figure class="mb-4 text-center text-md-start">
                                        <img src="<?php echo $image[0]; ?>" alt="<?php echo get_bloginfo( 'name' ); ?>" width="300" height="auto">
                                    </figure>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <form class="job-apply-form pr-4 ps-4" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="ad-id" value="<?php echo $ad_id; ?>">
                                    <div class="mb-3">
                                        <label for="firstname-<?php echo $ad_id; ?>" class="form-label"><?php _e( 'First name', 'bh2ojaa' ); ?> *</label>
                                        <input type="text" class="form-control" name="firstname" id="firstname-<?php echo $ad_id; ?>" placeholder="Enter your first name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="lastname-<?php echo $ad_id; ?>" class="form-label"><?php _e( 'Last name', 'bh2ojaa' ); ?> *</label>
                                        <input type="text" class="form-control" name="lastname" id="lastname-<?php echo $ad_id; ?>" placeholder="Enter your last name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email-<?php echo $ad_id; ?>" class="form-label"><?php _e( 'Email address', 'bh2ojaa' ); ?> *</label>
                                        <input type="email" class="form-control" name="email" id="email-<?php echo $ad_id; ?>" placeholder="Enter your email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tel-<?php echo $ad_id; ?>" class="form-label"><?php _e( 'Mobile', 'bh2ojaa' ); ?></label>
                                        <input type="tel" class="form-control" name="tel" id="tel-<?php echo $ad_id; ?>" maxlength="16" minlength="10" placeholder="Enter your phone number">
                                    </div>
                                    <div class="mb-3">
                                        <label for="gender-<?php echo $ad_id; ?>" class="form-label"><?php _e( 'Gender', 'bh2ojaa' ); ?></label>
                                        <select class="form-control" name="gender" id="gender-<?php echo $ad_id; ?>">
                                            <option value="">-- <?php _e( 'Select..', 'bh2ojaa' ); ?> --</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Prefer not to disclose">Prefer not to disclose</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="resume-<?php echo $ad_id; ?>" class="form-label"><?php _e( 'Resume/CV', 'bh2ojaa' ); ?></label>
                                        <input type="file" class="form-control bg-transparent border-0 image" multiple name="file" id="file">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="cover-note-<?php echo $ad_id; ?>" class="form-label"><?php _e( 'Cover Note', 'bh2ojaa' ); ?></label>
                                        <textarea class="form-control" name="cover-note" id="cover-note-<?php echo $ad_id; ?>" rows="3" placeholder="Cover note"></textarea>
                                    </div>
                                    <div class="form-message font-weight-bold fw-bold mb-3"></div>
                                    <button type="submit" class="job-apply-submit btn btn-primary"><?php _e( 'Submit', 'bh2ojaa' ); ?></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div> <!-- .container -->
</div> <!--#main-content -->
<?php get_footer(); ?>