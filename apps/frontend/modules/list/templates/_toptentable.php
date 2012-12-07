<table class="listtable">
  <tr>
    <td colspan="2">
      <strong><?php echo $list->name ?></strong>
      <br />
      <br />
    </td>
  </tr>
  <?php if (!isset($limit)) : ?>
    <?php $limit = 10 ?>
  <?php endif; ?>
  <?php $entities = $list->getEntitiesByRankQuery()->limit($limit)->execute() ?>
  <?php foreach($entities as $e) : ?>
    <tr>
      <td class ="rank">
        <?php if ($e->LsListEntity[0]->rank) : ?>
          <?php echo $e->LsListEntity[0]->rank ?>.
        <?php else : ?>
        <?php endif; ?>   
         
      </td>
      <td class="entity">
        <?php echo entity_link($e, '') ?>  
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (isset($see_more) && $see_more && $list->LsListEntity->count() > $limit) : ?>
    <tr>
      <td colspan="2" class="see_more">
        <em>
          <?php echo link_to('see more...', 'list/view?id=' . $list->id) ?>
        </em>      
      </td>    
    </tr>
  <?php endif; ?>
</table>