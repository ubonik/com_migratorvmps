<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
?>
<form action="<?php echo Route::_("index.php?option=com_migratorvmps&view=vmproducts") ?>"
      method="POST" name="adminForm" id="adminForm">     
    <div id="j-main-container" class="span10">
        <table class="table table-striped table-hover" >
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="30%"><?php echo Text::_('COM_MIGRATORVMPS_PRODUCT_NAME')?></th>
                    <th width="20%"><?php echo Text::_('COM_MIGRATORVMPS_CATEGORY')?></th>
                    <th width="30%"><?php echo Text::_('COM_MIGRATORVMPS_SHORT_DESCRIPTION') ?></th>
                    <th width="15%"><?php echo Text::_('COM_MIGRATORVMPS_LINK') ?></th>                                                     
                </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="5">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
            </tfoot>
            <tbody>
                <?php if(!empty($this->items)): ?>
                    <?php $i= 1; ?>
                    <?php foreach ($this->items as $key=>$item): ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?=$item->name; ?></td>                            
                            <td><?=$item->description_short; ?></td>
                            <td><?=$item->link_rewrite;  ?></td>
                                                       
                        </tr>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<input type="hidden" name="task" value="">
<input type="hidden" name="boxchecked" value="0">
<?php echo HTMLHelper::_('form.token') ?>
</form>

