<?php

/**
 * @file
 * Template to render a Ting collection of books.
 */
?>
<li class="clear-block">
  <?php if ($picture): ?>
    <div class="picture">
      <?php print $picture; ?>
    </div>
  <?php endif; ?>
  <div class="item graybox-btns<?php print $picture?'':' nopicture'; ?>">
    <a href="<?php print $collection->url; ?>">
      <h3><?php print $ting_title ?></h3>
      <?php if ($ting_creators) : ?>
      <span class="creator">
        <?php echo t('By %creator_name%', array('%creator_name%' => implode(', ', $ting_creators))) ?>
      </span>
      <?php endif; ?>
      <?php if ($ting_publication_date) : ?>
      <span class="publication_date">
        (<?php echo $ting_publication_date /* TODO: Improve date handling, localizations etc. */ ?>)
      </span>
      <?php endif; ?>
      <div class="types">
        <?php print $type_list; ?>
      </div>
      <?php if ($ting_abstract) : ?>
      <p class="abstract">
        <?php print check_plain($ting_abstract); ?>
      </p>
      <?php endif; ?>
    </a>
  </div>
</li>
