<link rel="stylesheet" type="text/css" href="{{asset('css/search-form.css')}}">

<section class="container">
    <form onsubmit="submitFn(this, event);">
        @csrf
        <div class="search-wrapper active">
            <div class="input-holder">
                <input type="text" class="search-input" placeholder="输入关键词，点击按钮搜索" />
                <button class="search-icon" onclick="searchToggle(this, event);"><span></span></button>
            </div>
        </div>
    </form>
</section>