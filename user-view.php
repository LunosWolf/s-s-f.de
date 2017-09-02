<?php namespace ProcessWire;
    $username = $input->urlSegmentStr;
	$profile = $users->get("name=$username");
?>
<? if ($profile instanceof User): ?>
    <?php
        if ($profile->isInvisible) {
            $session->redirect($pages->get('name=404')->url);
        }
        $userLink = $this->config->urls->root.'users/'.$profile->name;
        $profileImage = $profile->avatar->size(128,128);
    ?>
    <div class="profile-info">
        <div class="profile-info__column">
            <div layout="row" layout-nowrap>
                <h1><?=$profile->username?></h1>
                <img flex-end class="profile" src="<?=$profileImage->url?>" alt="Profilbild von <?=$profile->username?>">
            </div>
            <div class="actions">
                <? if($profile->name == $user->name || $user->hasRole('superuser')):?>
                    <a href="<?=$profile->editUrl?>">Profil bearbeiten</a>
                <? endif?>
            </div>
            <?php
                $events = $pages->find("template=event-registration,profile=$profile");
                foreach ($events as $event) {
                    echo $event->parent->parent->title;
                }
            ?>
            <?=$profile->renderFields()?>
        </div>
        <div class="profile-info__column">
            <event-list-short></event-list-short>
            <event-list-short></event-list-short>
        </div>
    </div>
<? else: ?>
	<?php
        $letter = $input->get->letter ? trim($sanitizer->name($input->get->letter), ', ') : '';
		$sort = $input->get->sort ? trim($sanitizer->name($input->get->sort), ', ') : 'username';
        $input->whitelist('sort',$sort);
		$input->whitelist('letter',$letter);
        $availableLetters = array_unique(
            array_map(
                function($user){
                    return strtoupper(substr($user->name, 0, 1));
                },
                $users->find('*')->getArray()
            )
        );
        sort($availableLetters);
		$profiles = $users->find("sort=$sort, isInvisible!=1, username^=$letter ");
		$profilesPager = $profiles->renderPager([
			'numPageLinks' => 5,
			'nextItemLabel' => '>',
		    'previousItemLabel' => '<',
		    'listMarkup' => '<ul class="pagination">{out}</ul>',
		    'itemMarkup' => '<li class="pagination__container {class}">{out}</li>',
		    'linkMarkup' => '<a class="pagination__item" href="{url}">{out}</a>',
			'separatorItemClass' => 'pagination__container--separator',
			'nextItemClass' => 'pagination__container--next',
			'previousItemClass' => 'pagination__container--previous',
			'lastItemClass' => 'pagination__container--last',
			'firstItemClass' => 'pagination__container--first',
			'currentItemClass' => 'pagination__container--current',
			'firstNumberItemClass' => 'pagination__container--firstNumber',
			'lastNumberItemClass' => 'pagination__container--lastNumber'
		]);
	?>
	<h1>User</h1>
	<span>
        Es sind bereits <strong><?=$users->count()?></strong> User registriert.
        <? if(!empty($letter)): ?>
            <strong><?=$profiles->count()?></strong> User die mit dem Buchstaben <strong><?=strtoupper($letter)?></strong> beginnen.
        <? endif ?>
    </span>
    <ul class="pagination">
        <? foreach($availableLetters as $availableLetter): ?>
            <li class="pagination__item"><a href="<?=$page->url?>?letter=<?=$availableLetter?>"><?=$availableLetter?></a></li>
        <? endforeach ?>
    </ul>
	<?=$profilesPager?>
        <user-profile-list>
            <div layout="row">
            	<? foreach ($profiles as $profile): ?>
                    <?php
                        $events = $pages->find("template=event-registration,profile=$profile")->getArray();
                    ?>
                    <user-profile flex="33"
                        joined="<?=date('Y-m-d', $profile->created)?>"
                        link="<?=$this->config->urls->root?>users/<?=$profile->name?>"
                        club-number="<?=$profile->clubNumber?>"
                        username="<?=$profile->username?>"
                        events="<?= join(',', array_map(function($event){return $event->parent->parent->title;}, $events));?>"
                        avatar="<?=$profile->avatar->size(64,64)->url?>"></user-profile>
            	<? endforeach ?>
            </div>
        </user-profile-list>
	<?=$profilesPager?>
<? endif ?>
