<?php

    $cards_shortcode_presets = \AgileStoreLocator\Helper::get_setting('cards_shortcode_presets', 'cards_shortcode_presets');
    $cards_shortcode_presets = is_serialized($cards_shortcode_presets) ? maybe_unserialize($cards_shortcode_presets) : [];
    $store_fields = ['title', 'logo', 'address', 'phone', 'email', 'direction', 'website'];
    $file = '';
    $default_enabled_fields = ['title', 'logo', 'phone', 'email', 'address'];
    $heading_tag = 'h2';
    $number_of_cards = 3;

    $store_array = [
        'title'             => 'Amanzi Club',
        'url'               => 'https://agilestorelocator.com/',
        'direction'         => 'Direction',
        'address'           => '45 North Street, Uitenhage, Eastern Cape, 5043, South Africa',
        'phone'             => '041 111 3964',
        'website'           => 'example.com',
        'email'             => 'john@example.com',
        'rating'            => '4.5',
        'path'              => ASL_URL_PATH . 'admin/images/example-logo.png',
        'title_phone_email' => true,
    ];

?>

<div class="asl-p-cont asl-new-bg asl-card-cont">
    <div class="hide">
        <svg xmlns="http://www.w3.org/2000/svg">
            <symbol id="i-plus" viewBox="0 0 32 32" width="13" height="13" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                <title><?php echo esc_attr__('Add','asl_locator') ?></title>
                <path d="M16 2 L16 30 M2 16 L30 16" />
            </symbol>
            <symbol id="i-trash" viewBox="0 0 32 32" width="13" height="13" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                <title><?php echo esc_attr__('Trash','asl_locator') ?></title>
                <path d="M28 6 L6 6 8 30 24 30 26 6 4 6 M16 12 L16 24 M21 12 L20 24 M11 12 L12 24 M12 6 L13 2 19 2 20 6" />
            </symbol>
            <symbol id="i-edit" viewBox="0 0 32 32" width="13" height="13" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                <title><?php echo esc_attr__('Edit','asl_locator') ?></title>
                <path d="M30 7 L25 2 5 22 3 29 10 27 Z M21 6 L26 11 Z M5 22 L10 27 Z" />
            </symbol>
            <symbol id="i-info" viewBox="0 0 32 32" width="13" height="13" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                <path d="M16 14 L16 23 M16 8 L16 10" />
                <circle cx="16" cy="16" r="14" />
            </symbol>
        </svg>
    </div>
    <div class="container">
        <div class="row asl-inner-cont">
            <div class="col-md-12">
                <div class="card p-0 mb-4">
                    <h3 class="card-title"><?php echo esc_attr__('Manage Cards', 'asl_locator') ?></h3>
                    <div class="card-body">
                        <?php if (!is_writable(ASL_UPLOAD_DIR . 'icon')) : ?>
                            <h6 class="alert alert-danger" style="font-size: 14px"><?php echo ASL_UPLOAD_DIR . 'icon' ?> <= <?php echo esc_attr__('Directory is not writable, marker image upload will fail, make directory writable.', 'asl_locator') ?></h6>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-md-12 ralign mb-4">
                                        <button type="button" data-toggle="smodal" data-target="#asl-add-card" id="new-shortcode" class="btn btn-success mrg-r-10"><i><svg width="13" height="13">
                                                    <use xlink:href="#i-plus"></use>
                                                </svg></i><?php echo esc_attr__('New Shortcode', 'asl_locator') ?></button>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="table-responsive mb-4">
                                        <table id="tbl_shortcode" class="display table table-striped table-bordered <?php echo !count($cards_shortcode_presets) ? 'd-none' : ''; ?>">
                                            <thead>
                                                <tr>
                                                    <th align="center"><?php echo esc_attr__('Shortcode', 'asl_locator') ?></th>
                                                    <th align="center"><?php echo esc_attr__('Action', 'asl_locator') ?>&nbsp;</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                                <?php if (count($cards_shortcode_presets)) : ?>
                                                    <?php foreach ($cards_shortcode_presets as $preset) : ?>
                                                        <?php
                                                            if (empty($preset)) continue;
                                                            $preset = str_replace('\"', '"', $preset);
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <span><?php echo $preset; ?></span>
                                                                <button type="button" class="copy-shortcode">
                                                                    <div class="alert fade"role="alert"></div>
                                                                    <svg width="23px" height="23px" viewBox="0 0 256 256" class="float-right" title="Copy" style="cursor: copy;" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M48.186 92.137c0-8.392 6.49-14.89 16.264-14.89s29.827-.225 29.827-.225-.306-6.99-.306-15.88c0-8.888 7.954-14.96 17.49-14.96 9.538 0 56.786.401 61.422.401 4.636 0 8.397 1.719 13.594 5.67 5.196 3.953 13.052 10.56 16.942 14.962 3.89 4.402 5.532 6.972 5.532 10.604 0 3.633 0 76.856-.06 85.34-.059 8.485-7.877 14.757-17.134 14.881-9.257.124-29.135.124-29.135.124s.466 6.275.466 15.15-8.106 15.811-17.317 16.056c-9.21.245-71.944-.49-80.884-.245-8.94.245-16.975-6.794-16.975-15.422s.274-93.175.274-101.566zm16.734 3.946l-1.152 92.853a3.96 3.96 0 0 0 3.958 4.012l73.913.22a3.865 3.865 0 0 0 3.91-3.978l-.218-8.892a1.988 1.988 0 0 0-2.046-1.953s-21.866.64-31.767.293c-9.902-.348-16.672-6.807-16.675-15.516-.003-8.709.003-69.142.003-69.142a1.989 1.989 0 0 0-2.007-1.993l-23.871.082a4.077 4.077 0 0 0-4.048 4.014zm106.508-35.258c-1.666-1.45-3.016-.84-3.016 1.372v17.255c0 1.106.894 2.007 1.997 2.013l20.868.101c2.204.011 2.641-1.156.976-2.606l-20.825-18.135zm-57.606.847a2.002 2.002 0 0 0-2.02 1.988l-.626 96.291a2.968 2.968 0 0 0 2.978 2.997l75.2-.186a2.054 2.054 0 0 0 2.044-2.012l1.268-62.421a1.951 1.951 0 0 0-1.96-2.004s-26.172.042-30.783.042c-4.611 0-7.535-2.222-7.535-6.482S152.3 63.92 152.3 63.92a2.033 2.033 0 0 0-2.015-2.018l-36.464-.23z" stroke="#979797" fill-rule="evenodd" />
                                                                    </svg>
                                                                </button>
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-danger btn-asl-delete btn-sm" data-target="#delete-shortcode" data-id="<?php echo esc_attr($file); ?>"><i><svg width="13" height="13">
                                                                    <use xlink:href="#i-trash"></use>
                                                                    </svg></i><?php echo esc_attr__('Delete', 'asl_locator') ?></button>
                                                                <button type="button" class="btn btn-success btn-asl-edit btn-sm" data-id="<?php echo esc_attr($file); ?>" data-toggle="smodal" data-target="#asl-edit-card"><i><svg width="13" height="13">
                                                                    <use xlink:href="#i-edit"></use>
                                                                    </svg></i><?php echo esc_attr__('Edit', 'asl_locator') ?></button>
                                                            </td>
                                                        <tr>

                                                    <?php endforeach; ?>
                                                <?php endif; ?>

                                            </tbody>
                                        </table>

                                        <div class="no-shortcode alert alert-warning <?php echo count($cards_shortcode_presets) ? 'd-none' : ''; ?>" role="alert">
                                            <?php echo esc_attr__('No Shortcode Preset is Available', 'asl_locator'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="dump-message asl-dumper"></div>

                                <div class="">
                                    <?php $store = (object) $store_array; ?>

                                    <div class="asl-cont asl-card-style asl-card-01">
                                        <div class="container">
                                            <div class="asl-list-cont">
                                                <div class="asl_TopTitleCard"><?php echo esc_attr__('Example Widget','asl_locator') ?>: <span><?php echo esc_attr__('Layout','asl_locator') ?> 1</span></div>
                                                <div class="pt-2"></div>
                                                <div class="row">
                                                    <?php for ($i=0; $i < $number_of_cards; $i++) : ?>
                                                        <div class="col-lg-4 col-md-6">
                                                            <?php include ASL_PLUGIN_PATH . 'public/partials/asl-card-01.php'; ?>
                                                        </div>
                                                    <?php endfor; ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="asl-cont asl-card-style asl-card-02">
                                        <div class="container">
                                            <div class="asl-list-cont">
                                                <div class="asl_TopTitleCard"><?php echo esc_attr__('Example Widget','asl_locator') ?>: <span><?php echo esc_attr__('Layout','asl_locator') ?> 2</span></div>
                                                <div class="pt-2"></div>
                                                <div class="row">
                                                    <?php for ($i=0; $i < $number_of_cards; $i++) : ?>
                                                        <div class="col-lg-4 col-md-6">
                                                            <?php include ASL_PLUGIN_PATH . 'public/partials/asl-card-02.php'; ?>
                                                        </div>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        
    <?php
        $modals = [
            'add' => 'Add',
            'edit' => 'Edit',
        ];
    ?>
    <?php foreach ($modals as $modal_key => $modal_value) : ?>
        <div class="smodal fade asl_manageCardModal <?php echo $modal_key; ?>" id="asl-<?php echo $modal_key; ?>-card" role="dialog">
            <div class="smodal-dialog" role="document">
                <div class="smodal-content">
                    <form id="frm-updatemarker" name="frm-updatemarker">
                        <div class="smodal-header">
                            <h5 class="smodal-title"><?php echo esc_attr__("$modal_value Card Shortcode", 'asl_locator') ?></h5>
                            <button type="button" class="close" data-dismiss="smodal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="smodal-body field-toggles">
                            <div class="row">

                                <div class="col-md-7">

                                    <div class="row">
                                        <?php $cards = \AgileStoreLocator\Helper::cards_tmpls(); ?>

                                        <div class="col-md-12 form-group mb-4">
                                            <label for="choose-card"><?php echo esc_attr__('Choose Card Layout', 'asl_locator') ?></label>
                                            <select style="width:100%" name="data[card]" class="custom-select choose-card">
                                                <?php for ($i=0; $i < count($cards); $i++) : ?>
                                                    <?php $temp_num = ($i + 1); ?>
                                                    <option value="<?php echo str_replace('asl-', '', $cards[$i]['value']); ?>"><?php echo esc_attr__('Card 0' . $temp_num, 'asl_locator') ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-12 form-group mb-4">
                                            <label for="choose-fields"><?php echo esc_attr__('Choose Fields', 'asl_locator') ?></label>
                                            <div class="fields-toggle row">
                                                <?php foreach ($store_fields as $field) : ?>
                                                    <?php $label = ucwords(str_replace('_', ' ', $field)); ?>
                                                    <div class="col-md-6">
                                                        <div class="form-group d-lg-flex d-md-block mb-4">
                                                            <label class="custom-control-label" for="asl-<?php echo $field . '-' . $modal_key; ?>"><?php echo esc_attr__($label, 'asl_locator') ?></label>
                                                            <div class="form-group-inner">
                                                                <label class="switch" for="asl-<?php echo $field . '-' . $modal_key; ?>"><input type="checkbox" value="1" class="custom-control-input" name="data[analytics]" id="asl-<?php echo $field . '-' . $modal_key; ?>" data-target="<?php echo $field; ?>" <?php echo in_array($field, $default_enabled_fields) ? 'checked' : ''; ?>><span class="slider round"></span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <?php $use_slider = false; ?>

                                        <div class="col-md-12 form-group mb-4">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group d-lg-flex d-md-block mb-4">
                                                        <label class="custom-control-label" for="asl-use-slider-<?php echo $modal_key; ?>"><?php echo esc_attr__('Use Slider', 'asl_locator') ?></label>
                                                        <div class="form-group-inner">
                                                            <label class="switch use-slider" for="asl-use-slider-<?php echo $modal_key; ?>"><input type="checkbox" value="1" class="custom-control-input" name="data[use-slider]" id="asl-use-slider-<?php echo $modal_key; ?>" data-target="slider" <?php echo $use_slider ? 'checked' : ''; ?>><span class="slider round"></span></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 form-group mb-4">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group d-lg-flex d-md-block">
                                                        <label class="custom-control-label" for="asl-heading-tag-<?php echo $modal_key; ?>"><?php echo esc_attr__('Heading&nbsp;Tag', 'asl_locator') ?></label>
                                                        <select style="width:100%" class="custom-select heading-tag" data-target="heading_tag" >
                                                            <option value="h2">&lt;h2&gt;</option>
                                                            <option value="h3">&lt;h3&gt;</option>
                                                            <option value="h4">&lt;h4&gt;</option>
                                                            <option value="h5">&lt;h5&gt;</option>
                                                            <option value="h6">&lt;h6&gt;</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                            $filters = [
                                                'cities'      => 'Cities',
                                                'states'      => 'States',
                                                'countries'   => 'Countries',
                                                'postal_code' => 'Postal Code',
                                                'limit'       => 'Limit',
                                                'offset'      => 'Offset',
                                            ];
                                        ?>
                                        <div class="col-md-12 form-group mb-2">
                                            <div class="asl_cardFilterBox">
                                                <p class="label"><?php echo esc_attr__('Data Filters', 'asl_locator') ?></p>
                                                <div class="filter-fields-container">
                                                    <div class="asl_cardFilterCtnBox">
                                                        <div class="form-group">
                                                            <label for="txt_filter-<?php echo $modal_key; ?>" class="d-none">><?php echo esc_attr__('Select Field', 'asl_locator') ?></label>
                                                            <select id="txt_filter-<?php echo $modal_key; ?>" style="width:100%" name="data[filter]" class="filter-field custom-select">
                                                                <option value=""><?php echo esc_attr__('Select Field', 'asl_locator') ?></option>
                                                                <?php foreach ($filters as $filter_key => $filter_value) : ?>
                                                                    <option value="<?php echo $filter_key; ?>"><?php echo esc_attr__($filter_value, 'asl_locator'); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="value" class="d-none"><?php echo esc_attr__('Value', 'asl_locator') ?></label>
                                                            <input id="" name="" class="form-control">
                                                        </div>
                                                        <div class="asl_cardFilterRemoveButton">
                                                            <label class="d-none"><?php echo esc_attr__('Remove Filter', 'asl_locator') ?></label>
                                                            <button title="Remove Clause"><i class="icon-close">x</i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="asl_noFilters text-center"><?php echo esc_attr__('No Filters are Applied', 'asl_locator') ?></div>
                                                <button type="button" class="btn btn-dark btn-sm mrg-r-10 mt-3 float-left btn-asl-add-field">
                                                    <i>
                                                        <svg width="13" height="13">
                                                            <use xlink:href="#i-plus"></use>
                                                        </svg>
                                                    </i>
                                                    <?php echo esc_attr__('New Field', 'asl_locator') ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5 class="label"><?php echo esc_attr__('Card Preview', 'asl_locator') ?></h5>

                                            <div class="asl_previewCard mb-4">
                                                <div class="cards">
                                                    <div class="asl-cont asl-card-style asl-card-01">
                                                        <div class="asl-list-cont card-preview card-01">
                                                            <div class="sl-list-item">
                                                                <div class="sl-item-ctn-box">
                                                                    <div class="asl-logo-box" data-field="logo">
                                                                        <a href="javascript:void(0);">
                                                                            <img src="<?php echo ASL_URL_PATH ?>admin/images/example-logo.png" alt="" class="img-fluid">
                                                                        </a>
                                                                    </div>
                                                                    <div class="asl-item-box text-left">
                                                                        <h2 data-field="title" class="asl-card-title <?php echo !in_array('title', $default_enabled_fields) ? 'hide' : ''; ?>">
                                                                            <a><?php echo $store_array['title']; ?></a>
                                                                        </h2>
                                                                        <span data-field="address" class="asl-addr <?php echo !in_array('address', $default_enabled_fields) ? 'hide' : ''; ?>"><?php echo $store_array['address']; ?></span>



                                                                    </div>
                                                                </div>

                                                                <div class="addr-loc addr-loc-style1">
                                                                    <ul>
                                                                        <li data-field="phone" class="<?php echo !in_array('phone', $default_enabled_fields) ? 'hide' : ''; ?>">
                                                                            <i class="icon-mobile-1"></i>
                                                                            <a><?php echo $store_array['phone']; ?></a>
                                                                        </li>
                                                                        <li data-field="email" class="<?php echo !in_array('email', $default_enabled_fields) ? 'hide' : ''; ?>">
                                                                            <i class="icon-mail"></i>
                                                                            <a><?php echo $store_array['email']; ?></a>
                                                                        </li>
                                                                    </ul>

                                                                    <div class="wpmb-rating">
                                                                        <span class="wpmb-stars">
                                                                            <div class="wpmb-stars-out icon-star">
                                                                                <div style="width:70%" class="wpmb-stars-in icon-star"></div>
                                                                            </div>
                                                                        </span>
                                                                        <span class="wpmb-rating-text">4.8 <?php echo esc_attr__('Average Rating','asl_locator') ?></span>
                                                                    </div>
                                                                </div>

                                                                <div class="addr-loc addr-loc-style1">
                                                                    <ul style="display: flex; flex-direction: column;">
                                                                        <?php $order = 1; ?>
                                                                        <?php foreach ($store_fields as $store_field) : ?>
                                                                            <?php if ( in_array($store_field, ['title', 'address', 'logo', 'direction', 'website', 'phone', 'email']) ) continue; ?>
                                                                            <li data-field="<?php echo $store_field; ?>" class="<?php echo !in_array($store_field, $default_enabled_fields) ? 'hide' : ''; ?>" style="order: <?php echo $order; ?>;">
                                                                                <i class="icon-<?php echo $store_field; ?>-1"></i>
                                                                                <a><?php echo ucwords( str_replace('_', ' ', $store_field) ) . ': ' . $store_array[$store_field]; ?></a>
                                                                            </li>
                                                                        <?php $order++; ?>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                </div>
                                                                <div class="sl-item-btn">
                                                                    <ul>
                                                                        <li data-field="direction" class="<?php echo !in_array('direction', $default_enabled_fields) ? 'hide' : ''; ?>"><a><?php echo esc_attr__('Get Direction','asl_locator') ?></a></li>
                                                                        <li data-field="website" class="<?php echo !in_array('website', $default_enabled_fields) ? 'hide' : ''; ?>"><a class="btn-solid"><?php echo esc_attr__('Websites','asl_locator') ?></a></li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="asl-cont asl-card-style asl-card-02">

                                                        <div class="asl-list-cont card-preview hide card-02">
                                                            <div class="sl-list-item">
                                                                <div class="sl-item-ctn-box">
                                                                    <div class="asl-logo-box" data-field="logo">
                                                                        <a href="javascript:void(0);">
                                                                            <img src="<?php echo ASL_URL_PATH ?>admin/images/example-logo.png" alt="" class="img-fluid">
                                                                        </a>
                                                                    </div>
                                                                    <div class="asl-item-box">
                                                                        <h2 data-field="title" class="asl-card-title <?php echo !in_array('title', $default_enabled_fields) ? 'hide' : ''; ?>">
                                                                            <a><?php echo $store_array['title']; ?></a>
                                                                        </h2>
                                                                        <span  ata-field="address" class="asl-addr <?php echo !in_array('address', $default_enabled_fields) ? 'hide' : ''; ?>"><?php echo $store_array['address']; ?></span>

                                                                        <div class="wpmb-rating">
                                                                            <span class="wpmb-stars">
                                                                                <div class="wpmb-stars-out icon-star">
                                                                                    <div style="width:70%" class="wpmb-stars-in icon-star"></div>
                                                                                </div>
                                                                            </span>
                                                                            <span class="wpmb-rating-text">4.8 <?php echo esc_attr__('Average Rating','asl_locator') ?></span>
                                                                        </div>

                                                                        <a data-field="phone" class="asl-prod-link <?php echo !in_array('phone', $default_enabled_fields) ? 'hide' : ''; ?>" href="#"><?php echo $store_array['phone']; ?></a>
                                                                        <a data-field="email" class="asl-prod-link <?php echo !in_array('email', $default_enabled_fields) ? 'hide' : ''; ?>" href="#">demo@example.com</a>

                                                                    </div>
                                                                </div>

                                                                <div class="addr-loc addr-loc-style1">
                                                                    <ul style="display: flex; flex-direction: column;">
                                                                        <?php $order = 1; ?>
                                                                        <?php foreach ($store_fields as $store_field) : ?>
                                                                            <?php if ( in_array($store_field, ['title', 'address', 'logo', 'direction', 'website', 'phone', 'email']) ) continue; ?>
                                                                            <li data-field="<?php echo $store_field; ?>" class="<?php echo !in_array($store_field, $default_enabled_fields) ? 'hide' : ''; ?>" style="order: <?php echo $order; ?>;">
                                                                                <i class="icon-<?php echo $store_field; ?>-1"></i>
                                                                                <a><?php echo ucwords( str_replace('_', ' ', $store_field) ) . ': ' . $store_array[$store_field]; ?></a>
                                                                            </li>
                                                                        <?php $order++; ?>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                </div>
                                                                <div class="sl-item-btn">
                                                                    <ul>
                                                                        <li data-field="direction" class="<?php echo !in_array('direction', $default_enabled_fields) ? 'hide' : ''; ?>"><a><?php echo esc_attr__('Get Direction','asl_locator') ?></a></li>
                                                                        <li data-field="website" class="<?php echo !in_array('website', $default_enabled_fields) ? 'hide' : ''; ?>"><a class="btn-solid"><?php echo esc_attr__('Websites','asl_locator') ?></a></li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <p id="message_update"></p>
                            </div>
                            <div class="smodal-footer">
                                <button class="btn btn-primary btn-start mrg-r-15 btn-asl-save" type="button" data-loading-text="<?php echo esc_attr__('Submitting ...', 'asl_locator') ?>"><?php echo esc_attr__('Save', 'asl_locator') ?></button>
                                <button type="button" class="btn btn-default" data-dismiss="smodal"><?php echo esc_attr__('Cancel', 'asl_locator') ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</div>
<!-- asl-cont end-->

<?php
    $all_configs['html']['filter_field'] = '
        <div class="asl_cardFilterCtnBox">
            <div class="form-group">
                <label for="txt_filter" class="d-none">' . esc_attr__('Select Field', 'asl_locator') . '</label>
                <select id="txt_filter" style="width:100%" name="data[filter]" class="filter-field custom-select">
                    <option value="">' . esc_attr__('Select Field', 'asl_locator') . '</option>
                    <option value="cities">' . esc_attr__('Cities', 'asl_locator') . '</option>
                    <option value="states">' . esc_attr__('States', 'asl_locator') . '</option>
                    <option value="countries">' . esc_attr__('Countries', 'asl_locator') . '</option>
                    <option value="postal_code">' . esc_attr__('Postal Code', 'asl_locator') . '</option>
                    <option value="limit">' . esc_attr__('Limit', 'asl_locator') . '</option>
                    <option value="offset">' . esc_attr__('Offset', 'asl_locator') . '</option>
                </select>
            </div>
            <div class="form-group">
                <label for="value" class="d-none">' . esc_attr__('Value', 'asl_locator') . '</label>
                <input id="" name="" class="form-control" spellcheck="false" data-ms-editor="true">
            </div>
            <div class="asl_cardFilterRemoveButton">
                <label class="d-none">' . esc_attr__('Remove Filter', 'asl_locator') . '</label>
                <button title="Remove Clause"><i class="icon-close">x</i></button>
            </div>
        </div>
    ';

    $all_configs['html']['table_row'] = '
    <tr>
        <td>
            <span>the_shortcode</span>
            <button type="button" class="copy-shortcode" style="position: relative;">
                <div class="alert fade" style="position: absolute; bottom: 10px; left: 50%; padding: 10px; transform: translateX(-50%);" role="alert"></div>
                <svg width="23px" height="23px" viewBox="0 0 256 256" class="float-right" title="Copy" style="cursor: copy;" xmlns="http://www.w3.org/2000/svg">
                    <path d="M48.186 92.137c0-8.392 6.49-14.89 16.264-14.89s29.827-.225 29.827-.225-.306-6.99-.306-15.88c0-8.888 7.954-14.96 17.49-14.96 9.538 0 56.786.401 61.422.401 4.636 0 8.397 1.719 13.594 5.67 5.196 3.953 13.052 10.56 16.942 14.962 3.89 4.402 5.532 6.972 5.532 10.604 0 3.633 0 76.856-.06 85.34-.059 8.485-7.877 14.757-17.134 14.881-9.257.124-29.135.124-29.135.124s.466 6.275.466 15.15-8.106 15.811-17.317 16.056c-9.21.245-71.944-.49-80.884-.245-8.94.245-16.975-6.794-16.975-15.422s.274-93.175.274-101.566zm16.734 3.946l-1.152 92.853a3.96 3.96 0 0 0 3.958 4.012l73.913.22a3.865 3.865 0 0 0 3.91-3.978l-.218-8.892a1.988 1.988 0 0 0-2.046-1.953s-21.866.64-31.767.293c-9.902-.348-16.672-6.807-16.675-15.516-.003-8.709.003-69.142.003-69.142a1.989 1.989 0 0 0-2.007-1.993l-23.871.082a4.077 4.077 0 0 0-4.048 4.014zm106.508-35.258c-1.666-1.45-3.016-.84-3.016 1.372v17.255c0 1.106.894 2.007 1.997 2.013l20.868.101c2.204.011 2.641-1.156.976-2.606l-20.825-18.135zm-57.606.847a2.002 2.002 0 0 0-2.02 1.988l-.626 96.291a2.968 2.968 0 0 0 2.978 2.997l75.2-.186a2.054 2.054 0 0 0 2.044-2.012l1.268-62.421a1.951 1.951 0 0 0-1.96-2.004s-26.172.042-30.783.042c-4.611 0-7.535-2.222-7.535-6.482S152.3 63.92 152.3 63.92a2.033 2.033 0 0 0-2.015-2.018l-36.464-.23z" stroke="#979797" fill-rule="evenodd" />
                </svg>
            </button>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-asl-delete btn-sm" data-target="#delete-shortcode" data-id=""><i><svg width="13" height="13">
                    <use xlink:href="#i-trash"></use>
                </svg></i>' . esc_attr__('Delete', 'asl_locator') . '</button>
            <button type="button" class="btn btn-success btn-asl-edit btn-sm" data-id="" data-toggle="smodal" data-target="#asl-edit-card"><i><svg width="13" height="13">
                    <use xlink:href="#i-edit"></use>
                </svg></i>' . esc_attr__('Edit', 'asl_locator') . '</button>
        </td>
    </tr>';
?>


<!-- SCRIPTS -->
<script type="text/javascript">
	var asl_configs = <?php echo json_encode($all_configs); ?>;

    var ASL_Instance = {
        manage_cards_url: '<?php echo admin_url() . 'admin.php?page=manage-asl-cards' ?>',
        url: '<?php echo ASL_UPLOAD_URL ?>'
    };

    window.addEventListener("load", function() {
        asl_engine.pages.manage_cards();
    });
</script>