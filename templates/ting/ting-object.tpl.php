<?php
/**
 * @file
 * Template to render objects from the Ting database.
 *
 * Available variables:
 * - $object: The TingClientObject instance we're rendering.
 * - $image: Image for the thing.
 * - $title: Main title.
 * - $other_titles: Also known as.
 * - $alternative_titles: Array of other alternative titles. May be empty;
 * - $creators: Authors of the item (string).
 * - $date: The date of the thing.
 * - $abstract: Short description.
 */
?>
<div id="ting-item-<?php print $ting_local_id; ?>" class="ting-item ting-item-full">
  <h1><?php print $ting_title; ?></h1>

  <!-- AVAILABILITY -->

  <?php
    // TODO: This should be refactored into the availability module.
    if (ting_object_is($object, 'limited_availability')): ?>
    <div class="ting-status waiting"><?php print t('waiting for data'); ?></div>
  <?php endif; ?>

  <?php if ($image): ?>
  <div class="picture">
    <?php print $image; ?>
  </div>
  <?php endif; ?>

  <div class="right-of-pic clear-block">

    <div class='creator'>
      <?php if (sizeof($ting_creators_links) == 1): ?>
        <span class='byline'><?php echo ucfirst(t('by')); ?></span>
        <?php print $ting_creators_links[0]; ?>
      <?php endif; ?>
      <?php if ($ting_publication_date): ?>
        <span class='date'>(<?php print $ting_publication_date; ?>)</span>
      <?php endif; ?>
    </div>

    <div class="abstract"><?php print $ting_abstract; ?></div>

    <?php if (isset($ting_series_links)): ?>
      <p class="series">
        <span class="label"><?php print t('Series:')?></span>
        <?php print theme('item_list', $ting_series_links, NULL, 'span'); ?>
      </p>
    <?php endif; ?>

    <?php if (isset($additional_main_content)):
      print drupal_render($additional_main_content);
    endif; ?>

    <!-- RESERVE BUTTON -->

    <?php if ($buttons) :?>
      <div class="ting-object-buttons">
      <?php print theme('item_list', $buttons, NULL, 'ul', array('class' => 'buttons')) ?>
      </div>
    <?php endif; ?>

  </div><!-- /.right-of-pic -->

  <!-- FLERE INFORMATION -->
  <h2 class="title-collapsible">
    <?php print t('More information'); ?>
  </h2>
  <div class="additional-info collapsible-info">
    <?php print $ting_details; ?>

    <?php if (isset($additional_content)):
      print drupal_render($additional_content);
    endif; ?>
  </div><!-- /additional-info -->
</div><!-- /#ting-item -->
