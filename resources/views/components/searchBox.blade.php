<link rel="stylesheet" type="text/css" href="{{asset('css/search-form.css')}}">

<section class="container">
    <div class="search-wrapper active">
        <div class="input-holder">
            <input type="text" name="keyword" class="search-input" placeholder="输入关键词，点击按钮搜索" value="{{$keyword ?? ""}}"/>
            <button class="search-icon"><span></span></button>
        </div>
    </div>
</section>