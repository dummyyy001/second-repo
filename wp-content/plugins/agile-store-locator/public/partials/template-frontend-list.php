<?php


$class = '';


$default_addr = (isset($all_configs['default-addr']))?$all_configs['default-addr']: '';

$container_class    = (isset($all_configs['full_width']) && $all_configs['full_width'])? 'sl-container-fluid': 'sl-container';
$geo_btn_class      = ($all_configs['geo_button'] == '1')?'asl-geo icon-direction':'icon-search';
$geo_btn_text       = ($all_configs['geo_button'] == '1')?__('Current Location', 'asl_locator'):__('Search', 'asl_locator');
$search_type_class  = ($all_configs['search_type'] == '1')?'asl-search-name':'asl-search-address';

if($all_configs['pickup'])
  $class .= ' sl-pickup-tmpl';


$btn_text = ($all_configs['geo_button'] == '1')?__('Current Location','asl_locator'):__('Search Location','asl_locator');

?>
<style type="text/css">
    <?php echo $css_code; ?>
    .asl-cont .asl-clear-btn {z-index:1;border: 0;background: transparent;position: absolute;top: 1px;bottom: 0;right: 45px;  outline: none;line-height: 14px; padding: 0px 0.4rem;}
    .rtl .asl-cont .asl-clear-btn {right: auto;left: 45px;}
    li.sl-no-item h2 {font-size: 36px; font-weight: 800;margin-bottom: 1rem;text-align: center;}
    li.sl-no-item p {font-size: 18px;color: inherit;text-align: center;margin-top: 1rem;margin-bottom: 3rem;}
</style>
<div id="asl-storelocator" class="asl-cont storelocator-main asl-template-list asl-bg-<?php echo $all_configs['color_scheme'] ?> asl-text-<?php echo $all_configs['font_color_scheme'].$class; ?>">
    <div class="sl-container">
        <div id="asl-panel" class="sl-row">
            <div class="asl-overlay" id="map-loading">
                <div class="white"></div>
                <div class="sl-loading">
                  <i class="animate-sl-spin icon-spin3"></i>
                  <?php echo asl_esc_lbl('loading') ?>
                </div>
            </div>
            <div class="asl-panel-inner pol-12">
                <div class="asl-filter-sec">
                    <div class="asl-search-cont">
                        <div class="asl-search-inner">
                            <div class="sl-row">
                                <div class="pol-12">
                                    <div class="asl-search-group">
                                        <div class="form-group asl-addr-search">
                                            <label for="sl-main-search" class="sr-only"><?php echo asl_esc_lbl('search_loc1') ?></label>
                                            <input id="sl-main-search" type="text" class="form-control asl-search-address rounded-left" placeholder="<?php echo asl_esc_lbl('search_loc1') ?>">
                                            <a title="Current Location" class="sl-geo-btn asl-geo">
                                                <svg width="20px" height="20px" viewBox="0 0 561 561" fill="#333">
                                                    <path d="M280.5,178.5c-56.1,0-102,45.9-102,102c0,56.1,45.9,102,102,102c56.1,0,102-45.9,102-102 C382.5,224.4,336.6,178.5,280.5,178.5z M507.45,255C494.7,147.9,410.55,63.75,306,53.55V0h-51v53.55 C147.9,63.75,63.75,147.9,53.55,255H0v51h53.55C66.3,413.1,150.45,497.25,255,507.45V561h51v-53.55 C413.1,494.7,497.25,410.55,507.45,306H561v-51H507.45z M280.5,459C181.05,459,102,379.95,102,280.5S181.05,102,280.5,102 S459,181.05,459,280.5S379.95,459,280.5,459z"></path>
                                                </svg>
                                            </a>
                                        </div>
                                        <?php if($all_configs['show_categories'] == '1'):?>
                                        <div class="form-group asl-cat-filed">
                                            <select multiple="multiple" class="asl-select-ctrl form-control rounded-left">
                                                <option></option>
                                            </select>
                                        </div> 
                                        <?php endif; ?>
                                        <a title="<?php echo asl_esc_lbl('search') ?>" class="asl-search-btn">
                                            <span><i class="icon-search"></i> <?php echo asl_esc_lbl('search') ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php if(isset($all_configs['address_ddl']) && $all_configs['address_ddl'] == '1'): ?>
                            <div class="sl-row asl-list-ddls asl-ddl-filters"></div>
                            <?php endif; ?>
                        </div>
                        <div class="asl-sort-list">
                            <div class="sl-row">
                                <div class="pol-md-6">
                                    <div class="sl-row">
                                        <?php if($all_configs['search_2']): ?>
                                          <div class="pol-md-6">
                                              <div class="search_filter asl-name-search">
                                                  <div class="asl-filter-cntrl">
                                                    <label for="asl-name-search-field" aria-label="<?php echo asl_esc_lbl('time_switch_label') ?>" title="<?php echo asl_esc_lbl('time_switch_label') ?>" class="asl-cntrl-lbl"><?php echo asl_esc_lbl('search_name') ?></label>
                                                    <div class="sl-search-group">
                                                      <input type="text" id="asl-name-search-field" placeholder="<?php echo asl_esc_lbl('search_name') ?>"  class="asl-search-name form-control isp_ignore">
                                                    </div>
                                                  </div>
                                              </div>
                                          </div>
                                        <?php endif ?>
                                        <?php 
                                        if(isset($filter_ddl))
                                        foreach ($filter_ddl as $key => $label) : ?>
                                          <div class="pol-md-6">
                                              <div class="asl-ddl-filters">
                                                  <div class="asl-filter-cntrl">
                                                    <label class="asl-cntrl-lbl" for="asl-<?php echo $key ?>"><?php echo $label; ?></label>
                                                    <div class="sl-dropdown-cont" id="<?php echo $key; ?>_filter">
                                                    </div>
                                                  </div>
                                              </div>
                                          </div>
                                        <?php endforeach; ?>
                                        <?php if(isset($all_attributes['brand']) && count($all_attributes['brand'])): ?>
                                        <div class="pol-12">
                                            <div class="asl-sort-left">
                                                <label for="sl-brand" class="asl-cntrl-lbl"><?php echo asl_esc_lbl('brands') ?></label>
                                                <ul class="asl-tab-filters">
                                                    <?php foreach ($all_attributes['brand'] as $brand): ?>
                                                    <li><a aria-label="<?php echo $brand->name; ?>" class="sl-tab-opt" data-id="<?php echo $brand->id ?>"><?php echo $brand->name; ?></a></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="pol-md-6">
                                    <div class="sl-row">
                                        <div class="pol-md-6 sl-dist-cont"></div>
                                        <div class="pol-md-6">
                                            <div class="form-group">
                                                <div class="asl-sort-right">
                                                    <label for="sl-sort-by" class="asl-cntrl-lbl"><?php echo asl_esc_lbl('sort_by') ?></label>
                                                    <select id="sl-sort-by" class="sl-sort-by form-control">
                                                        <option value=""><?php echo asl_esc_lbl('distance_title') ?></option>
                                                        <option value="title" selected=""><?php echo asl_esc_lbl('title') ?></option>
                                                        <option value="city"><?php echo asl_esc_lbl('cities') ?></option>
                                                        <option value="state"><?php echo asl_esc_lbl('states') ?></option>
                                                        <option value="cat"><?php echo asl_esc_lbl('categories_tab') ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="asl-stats">
                    <div class="sl-row asl-stats-inner pt-3 pb-3">
                        <div class="pol text-left">
                            <div class="Num_of_store">
                                <div class="count-result-text"><?php echo $all_configs['head_title'] ?>: <span class="count-result"></span></div>
                            </div>
                        </div>
                        <div class="pol text-right">
                            <a class="btn btn-asl asl-print-btn"><i class="icon-print"></i> <?php echo asl_esc_lbl('print') ?></a>
                        </div>
                    </div>
                </div>
                <div class="asl-list-cont">
                    <ul id="asl-stores-list" class="sl-list">
                    </ul>
                    <div class="sl-row">
                        <div class="pol-12">
                            <div class="sl-pagination nav mb-2 justify-content-center text-center">
                            </div>  
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>