<?php
// تنظیمات هدر و توضیحات
$SITE_TITLE = "سیـــنما";
$SITE_DESC = "جستجو و دانلود رایگان فیلم و سریال خارجی";
$SITE_URL = "https://max.imum.ir/cinema";
$API_KEY = "4F5A9C3D9A86FA54EACEDDD635185";
$SEARCH_URL = "https://server-hi-speed-iran.info/api/search";
$SERIE_URL  = "https://server-hi-speed-iran.info/api/serie/by/filtres";
$SERIE_SEASON_URL = "https://server-hi-speed-iran.info/api/season/by/serie";

function http_get_json(string $url): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30]);
    $res = curl_exec($ch);
    if ($res === false) return [];
    return json_decode($res,true) ?? [];
}

function search_with_fallback(string $query, string $apiKey): array {
    global $SEARCH_URL, $SERIE_URL;
    
    $encoded = rawurlencode($query);
    $searchRes = http_get_json("$SEARCH_URL/$encoded/$apiKey/");
    $results = $searchRes['posters'] ?? [];
    
    if( count($results) >= 5 )
        return $results;

    for( $page=0; $page <= 5; $page++ ) {
        $list = http_get_json("$SERIE_URL/0/created/$page/$apiKey");
        
        foreach( $list as $serie ) {
            if( stripos($serie['title'], $query) !== false ) {
                $serie['type'] = 'serie';
                $results[] = $serie;
            }
        }
    }
    
    $unique=[];
    
    foreach( $results as $item ) 
        $unique[$item['id']] = $item;
        
    return array_values($unique);
}

// ========== AJAX handler (unchanged) ==========
if( isset($_GET['ajax']) ) {
    
    header('Content-Type: application/json; charset=utf-8');
    
    if ( isset($_GET['q']) || isset($_GET['serie_id']) ) {
        
        if( isset($_GET['serie_id']) ) {
            $url = "$SERIE_SEASON_URL/" . intval($_GET['serie_id']) . "/$API_KEY/";
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30
            ]);
            
            $res = curl_exec($ch);
            curl_close($ch);
        
            echo $res;
            exit;
        }
        else {
            echo json_encode( search_with_fallback($_GET['q'], $API_KEY) );
            exit;
        }
    }
    else {
        http_response_code(400);
        exit;
    }
}

$page_title = "{$SITE_TITLE} | ";

if ( isset($_GET['id']) && isset($_GET['title']) && isset($_GET['year']) && isset($_GET['type']) ) {
    $page_title .= ucwords(htmlspecialchars($_GET['title'])) . ' (' . intval($_GET['year']) . ')';
    $og_title = $page_title;
} else {
    $page_title .= "{$SITE_DESC}";
    $og_title = $SITE_TITLE;
}

// ========== HTML starts here ==========
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-bs-theme="dark">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    
    <!-- Primary Meta Tags -->
    <title><?=$page_title ?></title>
    <meta name="title" content="<?=$page_title ?>" />
    <meta name="description" content="<?=$SITE_DESC ?>" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?=$SITE_URL ?>" />
    <meta property="og:title" content="<?=$og_title ?>" />
    <meta property="og:description" content="<?=$SITE_DESC ?>" />
    <meta property="og:image" content="<?=$SITE_URL ?>/assets/images/meta-tags.jpg" />
    
    <!-- X (Twitter) -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="<?=$SITE_URL ?>" />
    <meta property="twitter:title" content="<?=$og_title ?>" />
    <meta property="twitter:description" content="<?=$SITE_DESC ?>" />
    <meta property="twitter:image" content="<?=$SITE_URL ?>/assets/images/meta-tags.jpg" />
    
    <!-- Meta Tags Generated with https://metatags.io -->
    
    <!-- Style -->
    <link rel="stylesheet" href="<?=$SITE_URL ?>/assets/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="<?=$SITE_URL ?>/assets/style.css?_rnd=<?= mt_rand(100000, 999999) ?>">
    
    <!-- Script -->
    <script src="<?=$SITE_URL ?>/assets/bootstrap.bundle.min.js"></script>
    <script>
    function titleCase(str) {
        var splitStr = str.toLowerCase().split(' ');
        for (var i = 0; i < splitStr.length; i++) {
            splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);     
        }
        return splitStr.join(' ');
    }
    </script>
    
    
    <!-- Matomo -->
    <script>
        var _paq = window._paq = window._paq || [];
        /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u="//max.imum.ir/matomo/";
            _paq.push(['setTrackerUrl', u+'matomo.php']);
            _paq.push(['setSiteId', '2']);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
        })();
    </script>
    <!-- End Matomo Code -->

</head>
<body class="d-flex flex-column align-items-center">

    <!-- Header -->
    <header class="text-center my-3 p-3 pb-0 border-0 rounded-3" > 
        <h1 class="mb-3">
            <a href="<?= $SITE_URL; ?>" class="site-title text-light text-decoration-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-film film-icon text-warning bg-dark me-2" viewBox="0 0 16 16">
                    <path d="M0 1a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm4 0v6h8V1zm8 8H4v6h8zM1 1v2h2V1zm2 3H1v2h2zM1 7v2h2V7zm2 3H1v2h2zm-2 3v2h2v-2zM15 1h-2v2h2zm-2 3v2h2V4zm2 3h-2v2h2zm-2 3v2h2v-2zm2 3h-2v2h2z"/>
                </svg>
                <span><?php echo $SITE_TITLE;?></span>
            </a>
        </h1>
        <div class="small w-auto rounded-pill bg-dark bg-opacity-75 p-1 px-2" style="font-size:0.8rem;"><?php echo $SITE_DESC;?></div>
    </header>
    <!-- /Header -->

<?php
// ========== <Detail Page> ==========

if ( isset($_GET['id']) && isset($_GET['title']) && isset($_GET['year']) && isset($_GET['type']) ) {

?>

    <!-- Details -->
    <div class="container">
        <div id="detailContainer" class="glass bg-dark bg-opacity-75 p-3 w-100 overflow-hidden rounded-3">
            <div class="text-center p-3">
                <div class="spinner-border text-warning mb-2" role="status">
                    <span class="visually-hidden">بارگذاری...</span>
                </div>
                <p class="mt-2">در حال دریافت اطلاعات...</p>
            </div>
        </div>
    </div>
    <!-- /Details -->
    
    <!-- Toast Notification -->
    <div class="toast-container text-bg-dark">
        <div class="toast align-items-center" id="copyToast" role="alert">
            <div class="d-flex">
                <div class="toast-body mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-success me-2" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                    <span id="toastMessage"></span>
                </div>
                <button type="button" class="btn-close me-2 m-auto d-none" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    <!-- /Toast Notification -->

    <!-- Poster Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0 p-0 pb-2">
                    <h6 class="modal-title text-truncate" id="modalTitle"></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <img src="" class="img-fluid w-100 rounded-3 border border-light border-1 border-opacity-25" id="modalImage">
                </div>
            </div>
        </div>
    </div>
    <!-- /Poster Image Modal -->
    
    <script>
    
    function truncateText(text, limit=200){
        if (!text) return '';
        if (text.length<=limit) return text;
        const short = text.substring(0,limit);
        return `<span class="short-text">${short}...</span>&nbsp;<span class="more-link link-offset-2">بیشتر</span>
                <span class="full-text d-none">${ text.replace(/\n/g , "<br>") }</span>`;
    }

    function buildHeader(item){
        return `
            <div class="modal-header-blur border-top border-bottom border-light border-opacity-10 rounded-3" style="--img-url:url('${item.cover||item.image}')">
                <img src="${item.cover||item.image}" class="shadow" style="cursor:pointer">
                <div class="info position-relative">
                    <span class="fw-normal text-muted" style="font-size:0.8rem;">${item.type === 'serie' ? 'سریال' : 'فیلم'}</span>
                    <h3 class="item-title fw-bold mt-2">${titleCase(item.title)} <span class="fw-normal">(${item.year})</span></h3>
                    <div class="d-block mb-3 bg-text-warning mx-2 font-monospace small text-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill text-warning" viewBox="0 0 16 16">
                            <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                        </svg>
                        <span>IMDb ${item.imdb}</span>
                    </div>
                    <span class="w-auto text-bg-dark rounded px-3 small rounded-pill">${item.country?.map(c=>`<span>${c.title}</span>`).join('')||''}</span>
                </div>
            </div>`;
    }

    function buildDownloadButton(source, epTitle){
        return `
            <div class="dropdown download-btns w-100">
                <button class="btn btn-indigo dropdown-toggle d-block w-auto middle-truncate" type="button" data-bs-toggle="dropdown" aria-expanded="false">${titleCase(epTitle.replace(/<[^>]*>/g,'').trim())}${source.quality ? " (" + source.quality + ") " : ''}</button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="${source.url}" target="_blank">دانلود مستقیم</a>
                        <div class="text-truncate text-muted small px-3 d-md-none" style="font-size:0.5rem;">${source.quality??'HD'}</div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" data-action="copy-link" data-url="${source.url}">کپی نشانی فایل</a></li>
                    <li class="d-none"><small class="px-2">حجم: ${source.size||'نامشخص'}</small></li>
                </ul>
            </div>`;
    }
    
    function showToast(message) {
        const toastMessage = document.getElementById('toastMessage');
        toastMessage.textContent = message;
        
        const toastElement = document.getElementById('copyToast');
        const toast = new bootstrap.Toast(toastElement, {
            animation: true,
            autohide: true,
            delay: 1500
        });
        toast.show();
    }

    // get URL Parameters
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    const title = urlParams.get('title');
    const year = urlParams.get('year');
    const type = urlParams.get('type');

    if (id && title) {
        document.title = "<?= $page_title ?>"; // title + ' (' + year + ')';
        loadDetail(id, title, year, type);
    }

    async function loadDetail(id, title, year, type) {
        const container = document.getElementById('detailContainer');
        try {
            // search by title
            const res = await fetch(`?ajax=1&q=${encodeURIComponent(title)}`);
            const data = await res.json();
            const item = data.find(i => i.id == id && i.year == year);
            if (!item) {
                container.innerHTML = '<p class="text-center text-danger">موردی یافت نشد</p>';
                return;
            }
            await renderDetail(item, type);
        } catch (e) {
            container.innerHTML = '<p class="text-center text-danger">خطا در بارگذاری اطلاعات</p>';
        }
    }

    async function renderDetail(item, type) {
        const container = document.getElementById('detailContainer');
        if (type === 'serie') {
            // *** Serie (seasons) ***
            const res = await fetch(`?ajax=1&serie_id=${item.id}`);
            const seasons = await res.json();
            let html = buildHeader(item) + `
                <div class="section-title fw-bold text-warning mb-2">اطلاعات کلی</div>
                <div class="bg-dark rounded-3 p-3 small">
                    <table class="info-table table table-dark">
                        <tr>
                            <td>نام اصلی</td>
                            <td>${item.original_title||item.title}</td>
                        </tr>
                        <tr>
                            <td>امتیاز</td>
                            <td>${item.imdb}</td>
                        </tr>
                        <tr>
                            <td>نام پارسی</td>
                            <td>${item.title}</td>
                        </tr>
                        <tr>
                            <td>ژانر</td>
                            <td>${(item.genres||[]).map(g=>g.title).join(', ')}</td>
                        </tr>
                        <tr>
                            <td>کشور</td>
                            <td>${(item.country||[]).map(c=>c.title).join(', ')}</td>
                        </tr>
                        <tr>
                            <td>سال انتشار</td>
                            <td>${item.year}</td>
                        </tr>
                        <tr>
                            <td>زبان</td>
                            <td>${item.lang||'-'}</td>
                        </tr>
                        <tr>
                            <td>محصول شبکه</td>
                            <td>${item.network||'-'}</td>
                        </tr>
                        <tr>
                            <td>رده سنی</td>
                            <td>${item.age_limit||'-'}</td>
                        </tr>
                        <tr>
                            <td>قسمت‌ها</td>
                            <td>${seasons.reduce((acc,s)=>acc+s.episodes.length,0)} قسمت</td>
                        </tr>
                    </table>
                </div>
                    
                <div class="section-title fw-bold text-warning mb-2">جزئیات و خلاصه داستان</div>
                <div class="bg-dark rounded-3 p-3 small">${truncateText(item.description,200)}</div>
                    
                <div class="section-title fw-bold text-warning mb-2">فصل‌ها و لینک‌ها</div>
                <div class="accordion" id="seasonsAccordion">
            `;

            seasons.forEach((season,idx)=>{
                html += `
                    <div class="accordion-item mb-2 bg-dark rounded">
                        <h2 class="accordion-header" id="heading${idx}">
                            <button class="accordion-button collapsed bg-dark bg-opacity-10 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${idx}" aria-expanded="false" aria-controls="collapse${idx}">${season.title}</button>
                        </h2>
                        <div id="collapse${idx}" class="accordion-collapse collapse" aria-labelledby="heading${idx}" data-bs-parent="#seasonsAccordion">
                            <div class="accordion-body">${season.episodes.map(ep=>{
                                let btns='';
                                ep.sources?.forEach(s=>{
                                    btns+=buildDownloadButton(s, ep.title);
                                });
                    return `<div class="mb-2">${btns||'<small class="opacity-50">بدون لینک</small>'}</div>`;
                }).join('')}</div></div></div>`;
            });
            html += '</div>';
            container.innerHTML = html;
        } else {
            // *** Movie ***
            let sources = '';
            
            if (item.sources?.length) {
                sources = item.sources.map(s=>buildDownloadButton(s,item.title)).join('');
                sources = `<div class="d-flex flex-wrap">${sources}</div>`;
            } else {
                sources = '<p class="opacity-50">لینک دانلود موجود نیست</p>';
            }

            let html = buildHeader(item) + `
                <div class="section-title fw-bold text-warning mb-2">اطلاعات کلی</div>
                <div class="bg-dark rounded-3 p-3 small">
                    <table class="info-table table table-dark">
                        <tr>
                            <td>نام اصلی</td>
                            <td>${item.original_title||item.title}</td>
                        </tr>
                        <tr>
                            <td>امتیاز</td>
                            <td>${item.imdb}</td>
                        </tr>
                        <tr>
                            <td>نام پارسی</td>
                            <td>${item.title}</td>
                        </tr>
                        <tr>
                            <td>ژانر</td>
                            <td>${(item.genres||[]).map(g=>g.title).join(', ')}</td>
                        </tr>
                        <tr>
                            <td>کشور</td>
                            <td>${(item.country||[]).map(c=>c.title).join(', ')}</td>
                        </tr>
                        <tr>
                            <td>سال انتشار</td>
                            <td>${item.year}</td>
                        </tr>
                        <tr>
                            <td>زبان</td>
                            <td>${item.lang||'-'}</td>
                        </tr>
                    </table>
                </div>
                    
                <div class="section-title fw-bold text-warning mb-2">جزئیات و خلاصه داستان</div>
                <div class="bg-dark rounded-3 p-3 small">${truncateText(item.description,200)}</div>
                    
                <div class="section-title fw-bold text-warning mb-2">دانلود</div>
                ${sources}
            `;
            container.innerHTML = html;
        }
        
        // middle truncate of download button
        applyTruncation();

        // "more" and "copy link" button
        container.addEventListener('click', e => {
            if(e.target.classList.contains('more-link')){
                const parent = e.target.closest('div');
                const full = parent.querySelector('.full-text');
                const short = parent.querySelector('.short-text');
                
                if(full && short) {
                    full.classList.remove('d-none');
                    short.classList.add('d-none');
                    e.target.remove();
                }
            }
            if(e.target.dataset.action === 'copy-link'){
                navigator.clipboard.writeText(e.target.dataset.url)
                    .then(() => {
                        showToast('لینک کپی شد!');
                    })
                    .catch(err => {
                        console.error('خطا در کپی کردن متن: ', err);
                        showToast('خطا در کپی کردن لینک!');
                    });
            }
        });

        // poster image modal
        const posterImg = document.querySelector('.modal-header-blur img');
        const itemTitle = document.querySelector('.modal-header-blur .item-title');
        
        if (posterImg) {
            posterImg.addEventListener('click', function() {
                const modalImg = document.getElementById('modalImage');
                modalImg.src = this.src;
                
                const modalTitle = document.getElementById('modalTitle');
                modalTitle.textContent = itemTitle.textContent;
                
                const modal = new bootstrap.Modal(document.getElementById('imageModal'));
                modal.show();
            });
        }
    }
    
    
    // middle truncate (for buttons)
    function middleTruncate(text, maxLength) {
        if (text.length <= maxLength) return text;
        const half = Math.floor((maxLength - 3) / 2);
        const start = text.slice(0, half);
        const end = text.slice(-half);
        return `${start}...${end}`;
    }
    
    function applyTruncation() {
        const isMobile = window.matchMedia('(max-width: 767px)').matches;
        const buttons = document.querySelectorAll('.middle-truncate');
        buttons.forEach(btn => {
            if (!btn.hasAttribute('data-original')) {
                btn.setAttribute('data-original', btn.innerText);
            }
            const original = btn.getAttribute('data-original');
            if (isMobile) {
                // maximum characters: 36
                btn.innerText = middleTruncate(original, 36);
            } else {
                btn.innerText = original;
            }
        });
    }
    
    // on page resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(applyTruncation, 150);
    });
    
    // ========== /Details ==========
    
    </script>
    
<?php
// ========== </Detail Page> ==========
} else {
// ========== <Main Page (Search)> ==========
?>

    <div class="container">
        <div class="px-3 py-1 glass rounded-pill">
            <div class="bg-dark p-1 mx-auto search-box text-center rounded-pill">
                <input id="searchInput" class="form-control form-control-lg text-center rounded-pill small" autofocus="on"  placeholder="نام فیلم یا سریال...">
            </div>
        </div>
    </div>
    
    <div class="container">
        <div id="results" class="glass bg-dark bg-opacity-75 p-1 pb-0 mb-4 rounded-3 mx-auto d-none" style="max-width:660px;"></div>
    </div>
    
    <script>
    
    let timer;
    const input = document.getElementById('searchInput');
    const resultsBox = document.getElementById('results');
    
    input.addEventListener('input', ()=>{
        clearTimeout(timer);
        const q = input.value.trim();
        if (q.length<2) {
            resultsBox.classList.add('d-none'); 
            return;
        }
        timer = setTimeout(()=>search(q),400);
    });
    
    function showLoading(container, message) {
        container.innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border text-warning mb-2" role="status">
                    <span class="visually-hidden">بارگذاری...</span>
                </div>
                <p class="mt-2">${message}</p>
            </div>
        `;
    }
    
    async function search(q){
        resultsBox.classList.remove('d-none');
        
        // show loading before send request
        showLoading(resultsBox, 'در حال دریافت اطلاعات...');
        
        const res = await fetch(`?ajax=1&q=${encodeURIComponent(q)}`);
        const data = await res.json();
        
        // clear loading
        resultsBox.innerHTML='';
        
        if(!data.length){ 
            resultsBox.innerHTML = '<p class="text-center">نتیجه‌ای یافت نشد</p>'; 
            return;
        }
        data.forEach(item=>{
            const el = document.createElement('div');
            el.className = 'result-item bg-dark mb-3 border border-light border-opacity-10';
            el.innerHTML = `
                <img src="${item.image}" alt="${item.title}" class="shadow">
                <div class="position-relative">
                    <span class="badge text-bg-warning position-absolute rounded-1" style="top:10px;right:-50px;width:45px;font-size:x-small;">${item.type === 'serie' ? 'سریال' : 'فیلم'}</span>
                    <div class="mt-5">
                        <h5 class="fw-bold">${titleCase(item.title)} <small class="text-muted fw-normal">(${item.year})</small></h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
                                    <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                                </svg>
                                <span class="small font-monospace">IMDb ${item.imdb}</span>
                            </span>
                        </div>
                        <div class="small text-muted" style="font-size:0.7rem;">
                            ${item.country?.map(c => c.title).join('، ') || ''}
                        </div>
                    </div>
                </div>`;
            el.onclick=()=>{
                window.location.href = `?id=${item.id}&title=${encodeURIComponent(item.title)}&year=${item.year}&type=${item.type || 'movie'}`;
            };
            resultsBox.appendChild(el);
        });
    }
    
    </script>
    
<?php
// ========== </Main Page (Search)> ==========
}
?>
    <footer class="text-bg-success border rounded-pill w-auto my-4 px-3 py-1">
        <span class="small font-monospace" style="font-size:0.8rem;">Developed with 
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-suit-heart-fill text-danger" viewBox="0 0 16 16">
                <path d="M4 1c2.21 0 4 1.755 4 3.92C8 2.755 9.79 1 12 1s4 1.755 4 3.92c0 3.263-3.234 4.414-7.608 9.608a.513.513 0 0 1-.784 0C3.234 9.334 0 8.183 0 4.92 0 2.755 1.79 1 4 1"/>
            </svg> by <a href="mailto:h.dastangoo@gmail.com" class="text-light">Hadi</a> &copy 2026</span>
    </footer>
    
</body>
</html>
