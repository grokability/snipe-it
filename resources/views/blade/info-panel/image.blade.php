@aware(['infoPanelObj', 'img_path'])

@if (($img_path) && ($infoPanelObj->getImageUrl($img_path)))
    <a href="{{ $infoPanelObj->getImageUrl() }}" data-toggle="lightbox" data-type="image">
        <img src="{{ $infoPanelObj->getImageUrl() }}" class="profile-user-img img-responsive img-thumbnail" alt="{{ $infoPanelObj->name }}" style=" width: 100% !important; margin-bottom: 10px;">
    </a>
    <br>
@endif