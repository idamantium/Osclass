<?php

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
    <head>
        <?php osc_current_web_theme_path('head.php'); ?>
        <meta name="robots" content="index, follow" />
        <meta name="googlebot" content="index, follow" />
    </head>
    <body>
        <?php osc_current_web_theme_path('header.php'); ?>
        <div class="row shadow blurb">
        <div class="form_publish">
            <?php osc_current_web_theme_path('inc.search.php'); ?>
        </div>
        </div>

        <div class="content home">
            <div class="row" id="main">

            <div class="large-2 columns shadow blurb navigation" >
             
                    <?php if(osc_count_list_regions() > 0 ) { ?>
                    <div class="box location">
                        <h3><strong><?php _e("Location", 'modern'); ?></strong></h3>
                        <ul>

                            <?php while(osc_has_list_countries() ) { ?>
                        
                            <li><a href="<?php echo osc_list_country_url(); ?>"><?php echo osc_list_country_name(); ?></a> <em>(<?php echo osc_list_country_items(); ?>)</em></li>
                        <!-- IB   <?php while(osc_has_list_regions() ) { ?>

                            <li><a href="<?php echo osc_list_region_url(); ?>"><?php echo osc_list_region_name(); ?></a> <em>(<?php echo osc_list_region_items(); ?>)</em></li>
                        <?php } ?> -->
                        <?php } ?>
                        
                        </ul>
                    </div>
                    <?php } ?>
              
                </div>


                <div class="large-10 columns shadow blurb">
                  <div class="categories ">
                    <?php while ( osc_has_categories() ) { ?>
                        <div class="category large-4 columns clearfix">
                            <h1><strong><a class="category cat_<?php echo osc_category_id(); ?>" href="<?php echo osc_search_category_url(); ?>"><?php echo osc_category_name(); ?></a> <span>(<?php echo osc_category_total_items(); ?>)</span></strong></h1>
                            <?php if ( osc_count_subcategories() > 0 ) { ?>
                                <ul>
                                    <?php while ( osc_has_subcategories() ) { ?>
                                        <li><a class="category cat_<?php echo osc_category_id(); ?>" href="<?php echo osc_search_category_url(); ?>"><?php echo osc_category_name(); ?></a> <span>(<?php echo osc_category_total_items(); ?>)</span></li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                        </div>
                        <?php
                            if (($col==1 && $i==$col1_max_cat) || ($col==2 && $i==$col2_max_cat) || ($col==3 && $i==$col3_max_cat)) {
                                $i = 1;
                                $col++;
                                echo '</div>';
                                if($x < $total_categories) {
                                    echo '<div class="col c'.$col.'">';
                                }
                            } else {
                                $i++;
                            }
                            $x++;
                        ?>
                    <?php } ?>
                </div>
               </div>

                

               <div class="latest_ads shadow blurb">
                    <h1><strong><?php _e('Latest Listings', 'modern'); ?></strong></h1> 
                    <?php if( osc_count_latest_items() == 0) { ?>
                        <p class="empty"><?php _e('No Latest Listings', 'modern'); ?></p>
                    <?php } else { ?>
                        <table border="0" cellspacing="0">
                             <tbody>
                                <?php $class = "even"; ?>
                                <?php while ( osc_has_latest_items() ) { ?>
                                 <tr class="<?php echo $class. (osc_item_is_premium()?" premium":""); ?>">
                                        <?php if( osc_images_enabled_at_items() ) { ?>
                                         <td class="photo">
                                            <?php if( osc_count_item_resources() ) { ?>
                                                <a href="<?php echo osc_item_url(); ?>">
                                                    <img src="<?php echo osc_resource_thumbnail_url(); ?>" width="75" height="56" title="<?php echo osc_item_title(); ?>" alt="<?php echo osc_item_title(); ?>" />
                                                </a>
                                            <?php } else { ?>
                                                <img src="<?php echo osc_current_web_theme_url('images/no_photo.gif'); ?>" alt="" title="" />
                                            <?php } ?>
                                         </td>
                                        <?php } ?>
                                         <td class="text">
                                             <h3><a href="<?php echo osc_item_url(); ?>"><?php echo osc_item_title(); ?></a> (<?php echo osc_item_category(); ?>)</h3>
                                             <p><strong><?php if( osc_price_enabled_at_items() ) { echo osc_item_formated_price(); ?> - <?php } echo osc_item_country(); ?></strong> near <?php echo osc_item_region();?> </p>
                                             <p>Posted by <strong><?php echo osc_item_contact_name(); ?></strong> on <?php echo osc_format_date(osc_item_pub_date()); ?></p>
                                             <p><?php echo osc_highlight( strip_tags( osc_item_description() ) ); ?></p>
                                         </td>
                                     </tr>
                                    <?php $class = ($class == 'even') ? 'odd' : 'even'; ?>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php if( osc_count_latest_items() == osc_max_latest_items() ) { ?>
                        <p class='pagination'><?php echo osc_search_pagination(); ?></p>
                            <p class="see_more_link"><a href="<?php echo osc_search_show_all_url();?>"><strong><?php _e("See all offers", 'modern'); ?> &raquo;</strong></a></p>
                        <?php } ?>
                    <?php View::newInstance()->_erase('items'); } ?>
                </div>
            </div>
           
        </div>
        <?php osc_current_web_theme_path('footer.php'); ?>
    </body>
</html>