<?php
/**
 * @file
 * Display a ting objects as part of a list.
 *
 * Available variables:
 * - $object: The thing..
 * - $local_id: The local id if the thing.
 * - $type: Type of the thing.
 * - $image: Image.
 * - $date: The date of the thing.
 * - $creator: Primary author.
 * - $additional_creators: Other authors.
 * - $language: The language of the item.
 * - $more_link: Link to details page.
 */
?>
<!-- ting-list-item.tpl -->
<div id="ting-item-<?php print $ting_local_id; ?>" class="ting-item clearfix graybox-btns">
  <div class="content clearfix clear-block">
    <div class="picture">
      <?php if ($image) { ?>
        <?php print $image; ?>
      <?php } ?>
    </div>
    <div class="item">
      <a href="<?php print $ting_url ?>">
        <div class="info">
          <h3><?php print $ting_title; ?></h3>

          <?php if (!empty($ting_creators)) { ?>
            <span class="author">
              <em><?php echo t('by'); ?></em>
              <?php print array_shift($ting_creators) ?>
            </span>
          <?php } ?>

          <span class='date'>(<?php print $ting_publication_date; ?>)</span>

          <div>
          <?php if (isset($additional_content)) { print drupal_render($additional_content); } ?>
          </div>

          <div class='language'><?php echo t('Language') . ': ' . $ting_language; ?></div>

          <?php
            if (!empty($ting_creators)) {
              foreach ($ting_creators as $creator) {
                print "<p>" . $creator . "</p>";
              }
            }
          ?>
        </div>
      </a>
    </div>
  </div>
</div>
