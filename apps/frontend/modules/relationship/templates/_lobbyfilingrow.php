<tr class="text_small hover<?php echo $shaded ? ' shaded' : '' ?>">
  <td><?php echo Dateable::convertForDisplay($object['start_date']) ?></td>
  <td><?php echo LsNumber::makeReadable($object['amount'], '$') ?></td>
  <td>
    <?php $agencies = array() ?>
    <?php foreach (LobbyFilingTable::getAgenciesQuery($object)->setHydrationMode(Doctrine::HYDRATE_ARRAY)->execute() as $agency) : ?>
      <?php $agencies[] = entity_link($agency, null) ?>
    <?php endforeach; ?>
    <?php echo implode('<br /> ', $agencies) ?>
  </td>

  <td>
    <?php $issues = array() ?>
    <?php foreach ($object['LobbyIssue'] as $issue) : ?>
      <?php $issues[] = $issue['name'] ?>
    <?php endforeach; ?>
    <?php echo implode('<br /> ', $issues) ?>
  </td>
  <td>
    <?php $lobbyists = array() ?>
    <?php foreach ($object['Lobbyist'] as $lobbyist) : ?>
      <?php $lobbyists[] = entity_link($lobbyist, null) ?>
    <?php endforeach; ?>
    <?php echo implode('<br /> ', $lobbyists) ?>
  </td>
  <td>
    <?php echo link_to('Source', LobbyFilingTable::getSourceUrl($object)) ?>
  </td>
</tr>