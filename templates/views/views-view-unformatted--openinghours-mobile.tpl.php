<?php
/**
 * @file views-view-unformatted.tpl.php
 * Default simple view template to display a list of rows.
 *
 * @ingroup views_templates
 */
?>
<ul class="collapsible-list">
<?php foreach ($rows as $id => $row): ?>
  <li class="<?php print $classes[$id]; ?>">
    <?php print $row; ?>
  </li>
<?php endforeach; ?>
</ul>
