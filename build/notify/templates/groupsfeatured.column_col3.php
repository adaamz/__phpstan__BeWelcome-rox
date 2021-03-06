<div id="groups">
    <div>
        <h3><? echo $this->words->get('GroupsFeaturedGroups'); ?></h3>
        <form action="groups/featured" method="get">
            <input type="hidden" name="GroupsFeaturedInput" value="" id="GroupsFeaturedInput" />
        </form>
        <?php
        if ($this->search_result)
        {
            $name_order = (($this->result_order == "nameasc") ? 'namedesc' : 'nameasc');
            $member_order = (($this->result_order == "membersasc") ? 'membersdesc' : 'membersasc');
            $created_order = (($this->result_order == "createdasc") ? 'createddesc' : 'createdasc');
            $category_order = (($this->result_order == "categoryasc") ? 'categorydesc' : 'categoryasc');
            ?>
            <h4><?= $words->get('GroupsSearchOrder');?></h4>
            <p class="grey">
            <a class="grey" href="groups/featured?GroupsFeaturedInput=<?=$this->search_terms;?>&amp;Order=<?=$name_order;?>&Page=<?=$this->result_page;?>"><?= $words->get('GroupsOrderName'); ?></a>
            |
            <a class="grey" href="groups/featured?GroupsFeaturedInput=<?=$this->search_terms;?>&amp;Order=<?=$member_order;?>&Page=<?=$this->result_page;?>"><?= $words->get('GroupsOrderMembers'); ?></a>
            |
            <a class="grey" href="groups/featured?GroupsFeaturedInput=<?=$this->search_terms;?>&amp;Order=<?=$created_order;?>&Page=<?=$this->result_page;?>"><?= $words->get('GroupsOrderDate'); ?></a>
<?
// Categories link disabled until we have categories
//            |
//            <a class="grey" href="groups/search?GroupsSearchInput={$this->search_terms}&amp;Order={$category_order}&Page={$this->result_page}">Category</a>
?>
    </div> <!-- row -->           
<?

            foreach ($this->search_result as $group_data) : ?>
                <div class="groupbox clearfix">
                    <a href="groups/<?=$group_data->id ?>">
                        <img class="framed float_left"  width="80px" alt="group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ?>"/>
                    </a>
                    <div class="groupinfo">
                        <h4><a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a></h4>
                        <ul>
                            <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                            <li><?= $words->get('GroupsDateCreation');?>: <?=$group_data->created; ?></li>
                            <li><?= $words->get('GroupsNewForumPosts');?>: <?=$group_data->getNewForumPosts; ?></li>
                        </ul>
                    </div> <!-- groupinfo -->
                </div> <!-- groupbox clearfix-->
            <?php endforeach ; 
        }
        else
        {
            echo <<<HTML
            <p class="note">
            {$words->get('GroupSearchNoResults')}
            </p>
</div>
</div>
HTML;
        }
        ?>
