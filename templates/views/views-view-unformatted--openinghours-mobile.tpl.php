<?php
// $Id: views-view-unformatted.tpl.php,v 1.6 2008/10/01 20:52:11 merlinofchaos Exp $
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