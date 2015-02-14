
<!-- #nav: main navigation -->
<div id="nav">

<!-- son of suckerfish navigation (script to teach IE hover class used in dropdown menu-->
  <script type="text/javascript"><!--//--><![CDATA[//><!--

    sfHover = function() {
        var sfEls = document.getElementById("nav_main").getElementsByTagName("li");
        for (var i=0; i<sfEls.length; i++) {
            sfEls[i].onmouseover=function() {
                this.className+=" sfhover";
            }
            sfEls[i].onmouseout=function() {
                this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
            }
        }
    }
    if (window.attachEvent) window.attachEvent("onload", sfHover);

//--><!]]></script>

    <ul id="nav_main">
        <li id="logo">
          <a href="<?=$active_menu_item == ('main' || '') ? 'main' : ''; ?>">
            <img src="images/logo_index_top.png" alt="Be Welcome" />
          </a>
        </li>
        <li><a href="search"><?=$words->getBuffered('FindMembers')?></a>
            <ul>
                <li><a href="searchmembers"><?=$words->getBuffered('MapSearch')?></a></li>
                <li><a href="search"><?=$words->getBuffered('TextSearch')?></a></li>
                <li><a href="places"><?=$words->getBuffered('BrowseCountries')?></a></li>
            </ul>
        </li>
        <li><a href="community"><?=$words->getBuffered('CommunityMenu')?></a>
            <ul>
                <li><a href="forums" title="<?=$words->getSilent('AgoraTagLine')?>"><?=$words->getBuffered('CommunityDiscussions')?></a><?php echo $words->flushBuffer(); ?></li>
                <li><a href="groups/search" title="<?=$words->getSilent('GroupsTagLine')?>"><?=$words->getBuffered('Groups')?></a><?php echo $words->flushBuffer(); ?></li>
                <li><a href="activities"><?=$words->getBuffered('Activities')?></a></li>
                <?php // if ($logged_in) { ?>
                <li><a href="suggestions"><?=$words->getBuffered('Suggestions')?></a></li>
                <?php // } ?>
                <li><a href="trip"><?=$words->getBuffered('Trips')?></a></li>
                <li><a href="blog"><?=$words->getBuffered('Blogs')?></a></li>
                <li><a href="wiki"><?=$words->getBuffered('Wiki')?></a></li>
            </ul>
        </li>
        <li><a href="safety"><?=$words->getBuffered('Safety')?></a></li>
        <li <?php echo ((!$volunteer) ? 'class="last"' : '') ?>><a href="about"><?=$words->getBuffered('GetAnswers')?></a>
            <ul>
                <li><a href="faq"><?=$words->getBuffered('Faq')?></a></li>
                <li><a href="feedback"><?=$words->getBuffered('ContactUs')?></a></li>
                <li><a href="about/getactive"><?=$words->getBuffered('About_GetActive')?></a></li>
                <li><a href="donate"><?=$words->getBuffered('DonateLink')?></a></li>
            </ul>
        </li>
        <?php if (isset($volunteer) && $volunteer) { ?>
            <li class="last"><a href="volunteer"><?=$words->getBuffered('Volunteer')?></a>
                <?=$this->volunteerMenu() ?>
            </li>
        <? } ?>
        <?php if ($logged_in) { ?>
        <!--
        <li><a href="#"><span class="icon icon-search"></span></a>
            <ul>
                <li><?= $this->quicksearch() ?></li>
            </ul>
        </li>
        -->
        <?php } ?>
    </ul>
    <ul id="nav_right">
    <?php if ($logged_in) { ?>
        <li><a style="font-weight: normal" href="main"><?= $username ?></a>
            <ul>
                <li><a href="members/<?=$username?>"><?=$words->getBuffered('Profile')?></a></li>
                <li><a href="editmyprofile"><?=$words->getBuffered('EditMyProfile')?></a></li>
                <li><a href="mypreferences"><?=$words->getBuffered('MyPreferences')?></a></li>
                <li><a href="messages"><?=$words->getBuffered('MyMessages')?></a></li>
                <li><a href="mynotes"><?=$words->getBuffered('ProfileMyNotes')?></a></li>
                <li><a href="groups/mygroups"><?=$words->getBuffered('MyGroups')?></a></li>
                <li><a href="logout"><?=$words->getBuffered('Logout')?></a></li>
            </ul>
        </li>
        <li><a href="/messages"><i class="icon icon-inbox"></i> <span style="font-weight: normal" >[<?= $numberOfNewMessagees ?>]</span></a></li>
        <li><a href="/logout" style="line-height: 1.6em; padding: 1em 0.75em">&nbsp;<i class="icon icon-off" style="vertical-align: middle; font-size: 1.6em; font-weight: normal;"></i></a>
    <?php }  else { ?>
    <li><a href="<?= $login_url ?>#login-widget" id="header-login-link"><i class="icon icon-off"></i> <?php echo $words->getBuffered('Login'); ?></a><?php echo $words->flushBuffer(); ?></li>
    <li><a href="signup"><?php echo $words->getBuffered('Signup'); ?></a><?php echo $words->flushBuffer(); ?></li>
    <?php } ?>
    </ul>
</div> <!-- nav -->



