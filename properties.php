<?php
session_start();
include "db_connect.php";

$typeFilter = '';
$searchSQL = '1';

if (isset($_GET['type']) && $_GET['type'] !== '') {
    $type = mysqli_real_escape_string($conn, $_GET['type']);
    $typeFilter = " AND property_type = '$type'";
}
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $searchSQL = "(property_name LIKE '%$search%' OR address LIKE '%$search%' OR property_type LIKE '%$search%')";
}

$properties = mysqli_query($conn,
    "SELECT * FROM properties WHERE $searchSQL $typeFilter ORDER BY created_at DESC"
);

$showLanding = !isset($_GET['browse']) && !isset($_GET['search']) && !isset($_GET['type']);
$currentType = isset($_GET['type']) ? $_GET['type'] : '';
$currentSearch = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>HousingHub | Properties</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}

body{cursor:none;font-family:'Outfit',sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
/* ── FIXED HEADER — cannot scroll with content ─────────────── */
body { padding-top: 106px !important; }
header {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  width: 100% !important;
  z-index: 99999 !important;
  box-shadow: 0 2px 28px rgba(0,0,0,.28) !important;
}
nav { position: relative !important; z-index: 100000 !important; }
.dropdown { z-index: 100001 !important; }
.dd-menu { z-index: 100002 !important; }
@media(max-width:900px){ body { padding-top: 80px !important; } }
/* CURSOR */
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none!important;transform:translate(-50%,-50%);transition:width .25s,height .25s,background .3s;mix-blend-mode:difference}
#cur-ring{width:20px;height:20px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;position:fixed;z-index:99998;pointer-events:none!important;transform:translate(-50%,-50%);transition:width .45s cubic-bezier(.23,1,.32,1),height .45s}
#cur-trail{width:30px;height:30px;border:1px solid rgba(200,164,60,.15);border-radius:50%;position:fixed;z-index:99997;pointer-events:none!important;transform:translate(-50%,-50%);transition:width .7s,height .7s}
#cur-label{position:fixed;z-index:99999;pointer-events:none!important;font-family:'Outfit',sans-serif;font-size:10px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--gold);opacity:0;transition:opacity .3s;white-space:nowrap}
#cur-label.visible{opacity:1}
body.cursor-hover #cur-dot{width:8px;height:8px;background:#fff}
body.cursor-hover #cur-ring{width:20px;height:20px;border-color:var(--gold);background:rgba(200,164,60,.06)}
body.cursor-click #cur-dot{width:5px;height:5px}
body.cursor-click #cur-ring{width:28px;height:28px}

/* BG */
.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12) 0%,transparent 50%),var(--ink);animation:atmo 14s ease-in-out infinite alternate}
@keyframes atmo{0%{filter:brightness(1)}100%{filter:brightness(1.1) hue-rotate(6deg)}}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}
.z{position:relative;z-index:10}
.reveal{opacity:0;transform:translateY(28px);transition:opacity .8s ease,transform .8s ease}
.reveal.visible{opacity:1;transform:translateY(0)}

/* ═══ FIXED HEADER — same as all pages ═══════════════════════ */
header{
  position:fixed;top:0;left:0;right:0;
  
  z-index:99999;
  display:flex;justify-content:space-between;align-items:center;
  padding:18px 60px;
  background:var(--gold);
  backdrop-filter:blur(16px);
  -webkit-backdrop-filter:blur(16px);
  border-bottom:1px solid rgba(0,0,0,.12);
  box-shadow:0 2px 24px rgba(0,0,0,.22);
  animation:fadeDown .8s ease both;
  overflow:visible;cursor:auto;
}
header button,header a{cursor:pointer!important;}
@keyframes fadeDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
.header-logo{display:flex;align-items:center;gap:14px}
.logo-circle{width:65px;height:65px;border-radius:50%;object-fit:cover;border:2px solid var(--gb)}
.logo-text{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:var(--white);line-height:1}
.logo-slogan{font-size:14px;color:darkblue;font-style:italic;display:block;margin-top:3px}
nav{display:flex;align-items:center;gap:4px;overflow:visible;position:relative;z-index:100000}
nav>a{font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:darkblue;text-decoration:none;padding:8px 14px;transition:color .3s}
nav>a:hover{opacity:.8}
.dropdown{position:relative;overflow:visible;z-index:100001}
.dd-btn{display:block;font-family:'Outfit',sans-serif;font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:darkblue;background:none;border:none;padding:8px 14px;white-space:nowrap;cursor:pointer;transition:color .3s}
.dd-btn:hover,.dd-btn.open{color:var(--white)}
.dd-menu{display:none;position:absolute;top:calc(100% + 8px);left:0;min-width:230px;z-index:100002;background:rgba(4,9,26,.99);border:1px solid var(--gb);border-radius:5px;padding:6px 0;box-shadow:0 24px 60px rgba(0,0,0,.85)}
.dd-menu.open{display:block}
.dd-menu a{display:block;font-size:12px;font-weight:400;letter-spacing:1px;color:var(--muted);text-decoration:none;padding:11px 22px;transition:color .2s,background .2s;white-space:nowrap}
.dd-menu a:hover{color:var(--gold);background:rgba(200,164,60,.08)}
.dd-divider{height:1px;background:var(--border);margin:5px 0}

/* ═══ LANDING PAGE STYLES ═════════════════════════════════════ */
.lp-bg{position:fixed; ;inset:0;z-index:0;background:radial-gradient(ellipse 100% 60% at 75% 20%,rgba(14,90,200,.2) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 15% 90%,rgba(180,140,40,.13) 0%,transparent 50%),var(--ink);animation:atmo 14s ease-in-out infinite alternate}
.lp-grid{position:fixed;inset:0;z-index:1;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.hero-section{min-height:100vh;display:flex;align-items:center;padding:60px 68px 80px;gap:60px;position:relative;z-index:10}
.hero-left{flex:1;max-width:600px}
.hero-right{flex-shrink:0;width:360px;display:flex;flex-direction:column;gap:14px;opacity:0;animation:fadeRight 1s ease 1.1s both}
.lp-tag{display:inline-flex;align-items:center;gap:14px;font-size:11px;font-weight:500;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:28px;opacity:0;animation:fadeUp .8s ease .4s both}
.lp-tag::before{content:'';width:44px;height:1px;background:var(--gold)}
.lp-h1{font-family:'Cormorant Garamond',serif;font-size:clamp(58px,7.5vw,102px);font-weight:700;line-height:.93;color:var(--white);opacity:0;animation:fadeUp 1s ease .6s both}
.lp-h1 em{color:var(--gold);font-style:italic}
.lp-sub{margin-top:30px;font-size:15px;font-weight:300;line-height:1.85;color:var(--muted);max-width:440px;opacity:0;animation:fadeUp .9s ease .8s both}
.lp-search{margin-top:44px;display:flex;background:rgba(255,255,255,.05);border:1px solid rgba(200,164,60,.25);border-radius:4px;overflow:hidden;backdrop-filter:blur(20px);opacity:0;animation:fadeUp .9s ease 1s both;transition:border-color .3s,box-shadow .3s}
.lp-search:focus-within{border-color:var(--gold);box-shadow:0 0 0 3px rgba(200,164,60,.1)}
.lp-search input{flex:1;background:transparent;border:none;outline:none;padding:17px 22px;color:var(--white);font-family:'Outfit',sans-serif;font-size:14px}
.lp-search input::placeholder{color:rgba(255,255,255,.28)}
.lp-sdiv{width:1px;background:rgba(200,164,60,.18);margin:10px 0}
.lp-search select{background:var(--ink);border:none;outline:none;padding:17px 16px;color:var(--muted);font-family:'Outfit',sans-serif;font-size:13px;cursor:pointer}
.lp-search select option{background:var(--ink);color:var(--white)}
.lp-sbtn{background:var(--gold);border:none;padding:17px 32px;color:var(--ink);font-family:'Outfit',sans-serif;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;transition:background .3s;white-space:nowrap}
.lp-sbtn:hover{background:var(--gold-l)}
.lp-stats{margin-top:52px;display:flex;gap:48px;align-items:center;opacity:0;animation:fadeUp .9s ease 1.2s both}
.lp-sn{font-family:'Cormorant Garamond',serif;font-size:42px;font-weight:700;color:var(--gold);line-height:1}
.lp-sl{font-size:10px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-top:5px}
.lp-sdivider{width:1px;height:44px;background:rgba(255,255,255,.1)}
.cat-tile{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:4px;padding:20px 18px;text-decoration:none;color:var(--white);display:flex;align-items:center;gap:14px;transition:all .35s;position:relative;overflow:hidden}
.cat-tile::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(200,164,60,.1),transparent);opacity:0;transition:opacity .35s}
.cat-tile:hover{border-color:rgba(200,164,60,.4);transform:translateX(6px)}
.cat-tile:hover::after{opacity:1}
.cat-tile-icon{font-size:22px;flex-shrink:0}
.cat-tile-name{font-size:13px;font-weight:600;flex:1}
.cat-tile-count{font-family:'Cormorant Garamond',serif;font-size:11px;color:rgba(200,164,60,.6);letter-spacing:1px}
.cat-tile-arr{font-size:14px;color:var(--gold);opacity:0;transform:translateX(-8px);transition:all .3s}
.cat-tile:hover .cat-tile-arr{opacity:1;transform:translateX(0)}
.browse-all-btn{display:block;text-align:center;background:transparent;border:1px solid rgba(200,164,60,.3);border-radius:4px;padding:14px;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);text-decoration:none;transition:all .3s}
.browse-all-btn:hover{background:var(--gold);color:var(--ink);border-color:var(--gold)}
.marquee-section{border-top:1px solid rgba(200,164,60,.1);border-bottom:1px solid rgba(200,164,60,.1);padding:18px 0;overflow:hidden;background:rgba(200,164,60,.03);position:relative;z-index:10}
.marquee-track{display:flex;gap:60px;width:max-content;animation:marquee 28s linear infinite}
.marquee-track:hover{animation-play-state:paused}
@keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.marquee-item{display:flex;align-items:center;gap:16px;white-space:nowrap;font-size:12px;font-weight:500;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.35)}
.marquee-dot{width:4px;height:4px;background:var(--gold);border-radius:50%;opacity:.6}
.features-section{padding:120px 68px;position:relative;z-index:10}
.sec-label{display:inline-flex;align-items:center;gap:14px;font-size:11px;font-weight:500;letter-spacing:3px;text-transform:uppercase;color:var(--gold)}
.sec-label::before{content:'';width:32px;height:1px;background:var(--gold)}
.sec-title{font-family:'Cormorant Garamond',serif;font-size:clamp(38px,4.5vw,58px);font-weight:700;line-height:1.1;color:var(--white);margin-top:18px}
.sec-title em{color:var(--gold);font-style:italic}
.features-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:2px;margin-top:60px;border:1px solid rgba(255,255,255,.06);border-radius:6px;overflow:hidden}
.feat-card{background:rgba(255,255,255,.025);padding:44px 36px;position:relative;overflow:hidden;transition:background .4s;border-right:1px solid rgba(255,255,255,.05)}
.feat-card:last-child{border-right:none}
.feat-card::before{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--gold),transparent);transform:scaleX(0);transform-origin:left;transition:transform .5s cubic-bezier(.23,1,.32,1)}
.feat-card:hover{background:rgba(200,164,60,.04)}
.feat-card:hover::before{transform:scaleX(1)}
.feat-num{font-family:'Cormorant Garamond',serif;font-size:64px;font-weight:700;color:rgba(200,164,60,.08);line-height:1;position:absolute;top:20px;right:24px;transition:color .4s}
.feat-card:hover .feat-num{color:rgba(200,164,60,.18)}
.feat-icon{font-size:32px;margin-bottom:20px;display:block}
.feat-title{font-size:17px;font-weight:600;color:var(--white);margin-bottom:12px}
.feat-desc{font-size:14px;font-weight:300;color:var(--muted);line-height:1.75}
.showcase-section{padding:0 68px 120px;position:relative;z-index:10}
.showcase-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr;grid-template-rows:auto auto;gap:16px;margin-top:56px}
.showcase-card{position:relative;border-radius:6px;overflow:hidden;background:rgba(255,255,255,.03);border:1px solid var(--border);padding:36px 30px;transition:transform .4s,border-color .4s,background .4s;text-decoration:none;color:var(--white);display:block}
.showcase-card.big{grid-row:span 2;padding:52px 40px}
.showcase-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(200,164,60,.08) 0%,transparent 60%);opacity:0;transition:opacity .4s}
.showcase-card:hover{transform:translateY(-5px);border-color:rgba(200,164,60,.3);background:rgba(200,164,60,.03)}
.showcase-card:hover::before{opacity:1}
.sc-emoji{font-size:36px;display:block;margin-bottom:20px}
.sc-type{font-size:10px;font-weight:600;letter-spacing:2.5px;text-transform:uppercase;color:var(--gold);margin-bottom:10px}
.sc-title{font-family:'Cormorant Garamond',serif;font-size:28px;font-weight:700;color:var(--white);line-height:1.2;margin-bottom:14px}
.sc-desc{font-size:13px;font-weight:300;color:var(--muted);line-height:1.7}
.sc-tag{display:inline-block;margin-top:20px;background:rgba(200,164,60,.12);border:1px solid rgba(200,164,60,.25);border-radius:20px;padding:6px 14px;font-size:11px;font-weight:500;letter-spacing:1px;color:var(--gold)}
.sc-link{display:inline-flex;align-items:center;gap:8px;margin-top:24px;font-size:12px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);opacity:0;transform:translateY(6px);transition:all .3s}
.showcase-card:hover .sc-link{opacity:1;transform:translateY(0)}
.how-section{padding:120px 68px;background:rgba(255,255,255,.015);border-top:1px solid rgba(255,255,255,.05);border-bottom:1px solid rgba(255,255,255,.05);position:relative;z-index:10}
.how-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:0;margin-top:60px;position:relative}
.how-steps::before{content:'';position:absolute;top:28px;left:12.5%;right:12.5%;height:1px;background:linear-gradient(90deg,transparent,rgba(200,164,60,.25),rgba(200,164,60,.25),transparent)}
.how-step{padding:0 24px;text-align:center}
.step-circle{width:56px;height:56px;border-radius:50%;border:1.5px solid rgba(200,164,60,.35);background:rgba(200,164,60,.06);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--gold);position:relative;z-index:1;transition:all .4s}
.how-step:hover .step-circle{background:rgba(200,164,60,.15);border-color:var(--gold);transform:scale(1.1)}
.step-title{font-size:15px;font-weight:600;color:var(--white);margin-bottom:10px}
.step-desc{font-size:13px;font-weight:300;color:rgba(255,255,255,.4);line-height:1.7}
.testi-section{padding:120px 68px;position:relative;z-index:10}
.testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:56px}
.testi-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:6px;padding:36px 30px;position:relative;overflow:hidden;transition:border-color .4s,transform .4s}
.testi-card:hover{border-color:rgba(200,164,60,.25);transform:translateY(-4px)}
.testi-qm{position:absolute;top:-10px;left:20px;font-family:'Cormorant Garamond',serif;font-size:120px;font-weight:700;color:rgba(200,164,60,.06);line-height:1;pointer-events:none}
.testi-text{font-family:'Cormorant Garamond',serif;font-size:18px;font-style:italic;color:rgba(255,255,255,.75);line-height:1.7;position:relative;z-index:1}
.testi-author{margin-top:24px;display:flex;align-items:center;gap:14px}
.testi-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.3),rgba(14,90,200,.3));border:1px solid rgba(200,164,60,.2);display:flex;align-items:center;justify-content:center;font-size:16px}
.testi-name{font-size:13px;font-weight:600;color:var(--white)}
.testi-role{font-size:11px;font-weight:300;color:rgba(255,255,255,.35);letter-spacing:1px}
.testi-stars{font-size:12px;color:var(--gold);margin-top:4px}
.cta-section{padding:120px 68px;text-align:center;position:relative;overflow:hidden;z-index:10}
.cta-section::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 60% at 50% 50%,rgba(37,99,235,.12) 0%,transparent 70%)}
.cta-big{font-family:'Cormorant Garamond',serif;font-size:clamp(44px,6vw,80px);font-weight:700;line-height:1.0;color:var(--white);position:relative}
.cta-big em{color:var(--gold);font-style:italic}
.cta-big .str{-webkit-text-stroke:1.5px rgba(255,255,255,.25);color:transparent}
.cta-sub{margin-top:20px;font-size:15px;font-weight:300;color:var(--muted);position:relative}
.cta-btns{margin-top:48px;display:flex;gap:16px;justify-content:center;position:relative}
.cta-p{background:var(--gold);color:var(--ink);padding:18px 52px;border:none;border-radius:3px;font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;transition:all .3s;display:inline-block}
.cta-p:hover{background:var(--gold-l);transform:translateY(-3px);box-shadow:0 14px 40px rgba(200,164,60,.28)}
.cta-s{background:transparent;color:var(--white);padding:18px 52px;border:1px solid rgba(255,255,255,.2);border-radius:3px;font-size:13px;font-weight:500;letter-spacing:2px;text-transform:uppercase;text-decoration:none;transition:all .3s;display:inline-block}
.cta-s:hover{border-color:var(--gold);color:var(--gold);transform:translateY(-3px)}

/* ═══ BROWSE PAGE STYLES ══════════════════════════════════════ */
.browse-wrap{position:relative;z-index:10;min-height:100vh}
.browse-header{padding:60px 60px 40px;border-bottom:1px solid var(--border)}
.browse-header-inner{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:24px}
.browse-eyebrow{font-size:11px;font-weight:500;letter-spacing:3px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:12px;margin-bottom:12px}
.browse-eyebrow::before{content:'';width:30px;height:1px;background:var(--gold)}
.browse-title{font-family:'Cormorant Garamond',serif;font-size:clamp(36px,4vw,56px);font-weight:700;color:var(--white);line-height:1.1}
.browse-title em{color:var(--gold);font-style:italic}
.browse-count{font-size:13px;color:var(--muted);margin-top:8px}
.browse-count strong{color:var(--white)}
.browse-controls{padding:28px 60px;border-bottom:1px solid var(--border);background:rgba(255,255,255,.015);display:flex;gap:16px;align-items:center;flex-wrap:wrap}
.browse-search-box{flex:1;min-width:260px;display:flex;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:4px;overflow:hidden;transition:border-color .3s,box-shadow .3s}
.browse-search-box:focus-within{border-color:var(--gold);box-shadow:0 0 0 3px rgba(200,164,60,.08)}
.browse-search-box input{flex:1;background:transparent;border:none;outline:none;padding:13px 18px;color:var(--white);font-family:'Outfit',sans-serif;font-size:14px}
.browse-search-box input::placeholder{color:rgba(255,255,255,.22)}
.browse-search-box button{background:var(--gold);border:none;padding:13px 22px;color:var(--ink);font-family:'Outfit',sans-serif;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer;transition:background .3s;white-space:nowrap}
.browse-search-box button:hover{background:var(--gold-l)}
.type-filters{display:flex;gap:8px;flex-wrap:wrap}
.type-pill{display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:30px;border:1px solid var(--border);background:rgba(255,255,255,.03);color:var(--muted);font-size:12px;font-weight:500;letter-spacing:1px;text-decoration:none;transition:all .3s;white-space:nowrap}
.type-pill:hover{border-color:rgba(200,164,60,.4);color:var(--white);background:rgba(200,164,60,.06)}
.type-pill.active{border-color:var(--gold);color:var(--gold);background:rgba(200,164,60,.1)}
.type-pill-icon{font-size:14px}
.browse-body{padding:40px 60px 80px}
.prop-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:24px}
.prop-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:transform .35s,border-color .35s,box-shadow .35s;display:flex;flex-direction:column}
.prop-card:hover{transform:translateY(-6px);border-color:var(--gb);box-shadow:0 24px 60px rgba(0,0,0,.4)}
.prop-card-top{padding:28px 28px 0;display:flex;justify-content:space-between;align-items:flex-start}
.prop-type-badge{font-size:10px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--gold);background:rgba(200,164,60,.1);border:1px solid rgba(200,164,60,.2);padding:5px 12px;border-radius:20px}
.prop-purpose-badge{font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:5px 12px;border-radius:20px}
.prop-purpose-badge.rent{background:rgba(34,197,94,.12);color:#86efac;border:1px solid rgba(34,197,94,.2)}
.prop-purpose-badge.buy{background:rgba(59,130,246,.12);color:#93c5fd;border:1px solid rgba(59,130,246,.2)}
.prop-purpose-badge.lease{background:rgba(245,158,11,.12);color:#fcd34d;border:1px solid rgba(245,158,11,.2)}
.prop-card-body{padding:20px 28px 0;flex:1}
.prop-name{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--white);margin-bottom:6px;line-height:1.2}
.prop-address{font-size:13px;color:var(--muted);margin-bottom:18px;display:flex;align-items:center;gap:6px}
.prop-address::before{content:'📍';font-size:12px}
.prop-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px}
.prop-meta-item{background:rgba(255,255,255,.025);border-radius:6px;padding:10px 14px}
.prop-meta-label{font-size:10px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:4px}
.prop-meta-value{font-size:14px;font-weight:500;color:var(--white)}
.prop-price{padding:16px 28px;background:rgba(200,164,60,.05);border-top:1px solid rgba(200,164,60,.1);display:flex;align-items:center;justify-content:space-between}
.prop-price-label{font-size:10px;font-weight:500;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3)}
.prop-price-value{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--gold)}
.prop-price-sub{font-size:11px;color:var(--muted)}
.prop-amenities{padding:14px 28px;border-top:1px solid var(--border)}
.prop-amenities-label{font-size:10px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:8px}
.prop-amenity-tags{display:flex;flex-wrap:wrap;gap:6px}
.prop-amenity-tag{font-size:11px;color:rgba(255,255,255,.55);background:rgba(255,255,255,.04);border:1px solid var(--border);padding:4px 10px;border-radius:12px}
.prop-card-actions{padding:20px 28px;border-top:1px solid var(--border);display:flex;gap:10px}
.prop-btn{flex:1;text-align:center;padding:11px 16px;border-radius:6px;text-decoration:none;font-family:'Outfit',sans-serif;font-size:12px;font-weight:600;letter-spacing:1px;text-transform:uppercase;transition:all .3s;border:1px solid transparent}
.prop-btn.view{background:rgba(255,255,255,.06);color:var(--white);border-color:var(--border)}
.prop-btn.view:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2)}
.prop-btn.rent{background:rgba(34,197,94,.15);color:#86efac;border-color:rgba(34,197,94,.25)}
.prop-btn.rent:hover{background:rgba(34,197,94,.25)}
.prop-btn.buy{background:rgba(59,130,246,.15);color:#93c5fd;border-color:rgba(59,130,246,.25)}
.prop-btn.buy:hover{background:rgba(59,130,246,.25)}
.prop-btn.lease{background:rgba(245,158,11,.15);color:#fcd34d;border-color:rgba(245,158,11,.25)}
.prop-btn.lease:hover{background:rgba(245,158,11,.25)}
.empty-state{text-align:center;padding:100px 40px;grid-column:1/-1}
.empty-icon{font-size:56px;margin-bottom:20px;opacity:.4}
.empty-state h3{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:700;color:var(--white);margin-bottom:10px}
.empty-state p{font-size:14px;color:var(--muted);margin-bottom:28px}
.empty-state a{display:inline-block;padding:12px 32px;background:var(--gold);color:var(--ink);border-radius:4px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;transition:all .3s}
.empty-state a:hover{background:var(--gold-l)}

@keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeRight{from{opacity:0;transform:translateX(40px)}to{opacity:1;transform:translateX(0)}}

footer{padding:28px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}

@media(max-width:900px){
  body{padding-top:80px;cursor:auto}
  header{padding:14px 20px}
  nav{flex-wrap:wrap;gap:2px}
  .hero-section{padding:60px 24px 60px;flex-direction:column}
  .hero-right{width:100%}
  .browse-header,.browse-controls,.browse-body{padding-left:24px;padding-right:24px}
  .prop-grid{grid-template-columns:1fr}
  .features-grid{grid-template-columns:1fr}
  .showcase-grid{grid-template-columns:1fr}
  .showcase-card.big{grid-row:span 1}
  .how-steps{grid-template-columns:1fr 1fr;gap:40px}
  .how-steps::before{display:none}
  .testi-grid{grid-template-columns:1fr}
  .features-section,.showcase-section,.how-section,.testi-section,.cta-section{padding:80px 24px}
  #cur-dot,#cur-ring,#cur-trail,#cur-label{display:none}
}
@media(max-width:600px){
  .how-steps{grid-template-columns:1fr}
}
</style>
</head>
<body>

<div id="cur-dot"></div>
<div id="cur-ring"></div>
<div id="cur-trail"></div>
<div id="cur-label"></div>
<div class="page-bg"></div>
<div class="page-grid"></div>

<!-- HEADER -->
<header class="z">
  <div class="header-logo">
    <img src="image/hub.jpg" alt="Logo" class="logo-circle">
    <div><h1 class="logo-text">HOUSING HUB</h1><span class="logo-slogan">"Your Property, Our Priority"</span></div>
  </div>
  <nav>
  <a href="properties.php#features-section">Features</a>
  <div class="dropdown">
    <button class="dd-btn">Properties ▾</button>
    <div class="dd-menu">
      <a href="properties.php?browse=1">All Properties</a>
      <div class="dd-divider"></div>
      <a href="properties.php?type=Commercial">🏢 Commercial</a>
      <a href="properties.php?type=Residential">🏠 Residential</a>
      <a href="properties.php?type=Industrial">🏭 Industrial</a>
      <a href="properties.php?type=Agricultural">🌾 Agricultural</a>
      <a href="properties.php?type=Special+Purpose">🏛️ Special Purpose</a>
      <a href="properties.php?type=Land">🗺️ Land</a>
    </div>
  </div>
  <a href="properties.php#how">How It Works</a>
  <a href="logout.php">Logout</a>
</nav>
</header>

<?php if($showLanding): ?>
<!-- ═══ LANDING PAGE ═══════════════════════════════════════════ -->
<div class="lp-bg"></div>
<div class="lp-grid"></div>

<section class="hero-section z">
  <div class="hero-left">
    <div class="lp-tag">Uganda's Premier Property Platform</div>
    <h1 class="lp-h1">Find Your<br><em>Perfect</em><br><em>Space</em></h1>
    <p class="lp-sub">From Kampala offices to countryside farms — HousingHub connects you to the best commercial, residential, industrial, and agricultural properties across Uganda. Rent, buy, or lease with total confidence.</p>
    <form class="lp-search" action="properties.php" method="GET">
      <input type="text" name="search" placeholder="Search by name, address, type…">
      <div class="lp-sdiv"></div>
      <select name="type">
        <option value="">All Types</option>
        <option value="Commercial">Commercial</option>
        <option value="Residential">Residential</option>
        <option value="Industrial">Industrial</option>
        <option value="Agricultural">Agricultural</option>
        <option value="Special Purpose">Special Purpose</option>
        <option value="Land">Land</option>
      </select>
      <button type="submit" class="lp-sbtn">Search →</button>
    </form>
    <div class="lp-stats">
      <div><div class="lp-sn" id="sn1">0</div><div class="lp-sl">Properties</div></div>
      <div class="lp-sdivider"></div>
      <div><div class="lp-sn" id="sn2">0</div><div class="lp-sl">Types</div></div>
      <div class="lp-sdivider"></div>
      <div><div class="lp-sn" id="sn3">0</div><div class="lp-sl">Locations</div></div>
      <div class="lp-sdivider"></div>
      <div><div class="lp-sn" id="sn4">0</div><div class="lp-sl">Happy Clients</div></div>
    </div>
  </div>
  
</section>

<div class="marquee-section z">
  <div class="marquee-track">
    <div class="marquee-item"><span class="marquee-dot"></span>Commercial Properties</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Residential Homes</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Industrial Spaces</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Agricultural Land</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Special Purpose Buildings</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Open Land Plots</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Rent · Buy · Lease</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Verified Listings</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Transparent Pricing</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Commercial Properties</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Residential Homes</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Industrial Spaces</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Agricultural Land</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Special Purpose Buildings</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Open Land Plots</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Rent · Buy · Lease</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Verified Listings</div>
    <div class="marquee-item"><span class="marquee-dot"></span>Transparent Pricing</div>
  </div>
</div>

<section class="features-section z" id="features-section">
  <div class="reveal"><div class="sec-label">Why HousingHub</div><h2 class="sec-title">Built for Uganda's<br><em>Property Market</em></h2></div>
  <div class="features-grid reveal">
    <div class="feat-card"><div class="feat-num">01</div><span class="feat-icon">✅</span><div class="feat-title">Verified Listings</div><div class="feat-desc">Every property on HousingHub is reviewed before going live. You browse real, available properties — no ghosts, no scams.</div></div>
    <div class="feat-card"><div class="feat-num">02</div><span class="feat-icon">💎</span><div class="feat-title">Transparent Pricing</div><div class="feat-desc">Rent amounts, sale prices and lease terms are all displayed upfront in UGX. No hidden fees, no nasty surprises at signing.</div></div>
    <div class="feat-card"><div class="feat-num">03</div><span class="feat-icon">🏷️</span><div class="feat-title">Full Amenity Details</div><div class="feat-desc">See every included amenity — parking, generator, water, security — with cost type clearly marked before you commit.</div></div>
    <div class="feat-card"><div class="feat-num">04</div><span class="feat-icon">⚡</span><div class="feat-title">Instant Transactions</div><div class="feat-desc">Rent, buy or lease directly through the platform. Our seamless payment flow gets you from browsing to secured in minutes.</div></div>
    <div class="feat-card"><div class="feat-num">05</div><span class="feat-icon">🔍</span><div class="feat-title">Smart Search &amp; Filter</div><div class="feat-desc">Find exactly what you need — filter by type, search by name or address, and browse by purpose: rent, buy, or lease.</div></div>
    <div class="feat-card"><div class="feat-num">06</div><span class="feat-icon">🇺🇬</span><div class="feat-title">Uganda-Focused</div><div class="feat-desc">Prices in UGX, locations you know, property types that match the local market. Built specifically for Ugandan real estate.</div></div>
  </div>
</section>

<section class="showcase-section z" id="showcase">
  <div class="reveal"><div class="sec-label">Property Types</div><h2 class="sec-title">Spaces <em>You Could Need</em></h2></div>
  <div class="showcase-grid reveal">
    <a href="properties.php?type=Commercial" class="showcase-card big" data-label="Browse"><span class="sc-emoji">🏢</span><div class="sc-type">Commercial</div><div class="sc-title">Offices, Shops &amp; Business Premises</div><div class="sc-desc">Prime commercial spaces in Kampala and beyond. Whether opening a shop, setting up a head office, or expanding your business — we have the space.</div><span class="sc-tag">Available for Rent · Buy · Lease</span><div class="sc-link">Explore Commercial →</div></a>
    <a href="properties.php?type=Residential" class="showcase-card" data-label="Browse"><span class="sc-emoji">🏠</span><div class="sc-type">Residential</div><div class="sc-title">Homes &amp; Apartments</div><div class="sc-desc">Comfortable, secure homes for families and individuals across Uganda.</div><div class="sc-link">Explore →</div></a>
    <a href="properties.php?type=Land" class="showcase-card" data-label="Browse"><span class="sc-emoji">🗺️</span><div class="sc-type">Land</div><div class="sc-title">Open Plots &amp; Development Land</div><div class="sc-desc">Raw land and titled plots ready for development or investment.</div><div class="sc-link">Explore →</div></a>
    <a href="properties.php?type=Industrial" class="showcase-card" data-label="Browse"><span class="sc-emoji">🏭</span><div class="sc-type">Industrial</div><div class="sc-title">Warehouses &amp; Factories</div><div class="sc-desc">Large-scale industrial facilities built for heavy operations.</div><div class="sc-link">Explore →</div></a>
    <a href="properties.php?type=Agricultural" class="showcase-card" data-label="Browse"><span class="sc-emoji">🌾</span><div class="sc-type">Agricultural</div><div class="sc-title">Farmland &amp; Plantations</div><div class="sc-desc">Fertile land across Uganda's most productive regions.</div><div class="sc-link">Explore →</div></a>
  </div>
</section>

<section class="how-section z" id="how">
  <div class="reveal"><div class="sec-label">The Process</div><h2 class="sec-title">Simple Steps to<br><em>Your Next Space</em></h2></div>
  <div class="how-steps reveal">
    <div class="how-step"><div class="step-circle">1</div><div class="step-title">Browse Properties</div><div class="step-desc">Search by name, filter by type or scroll through all available listings on one clean page.</div></div>
    <div class="how-step"><div class="step-circle">2</div><div class="step-title">View Details</div><div class="step-desc">Check address, size, rooms, amenities, pricing and purpose — all clearly listed on every card.</div></div>
    <div class="how-step"><div class="step-circle">3</div><div class="step-title">Choose Your Action</div><div class="step-desc">Each property shows the right button — Rent, Buy, or Lease — so you always know what's available.</div></div>
    <div class="how-step"><div class="step-circle">4</div><div class="step-title">Complete Payment</div><div class="step-desc">Head straight to our payment page and secure your property quickly and safely.</div></div>
  </div>
</section>

<section class="testi-section z">
  <div class="reveal"><div class="sec-label">What People Say</div><h2 class="sec-title">Real <em>Feedback</em></h2></div>
  <div class="testi-grid reveal">
    <div class="testi-card"><div class="testi-qm">"</div><div class="testi-text">I found and secured my office space in Kampala within two days. The listings are real and the pricing was exactly as shown.</div><div class="testi-author"><div class="testi-avatar">👨🏾‍💼</div><div><div class="testi-name">David Okullo</div><div class="testi-role">Business Owner · Kampala</div><div class="testi-stars">★★★★★</div></div></div></div>
    <div class="testi-card"><div class="testi-qm">"</div><div class="testi-text">Finally a property platform that shows amenities upfront. I knew exactly what I was getting before I even contacted anyone.</div><div class="testi-author"><div class="testi-avatar">👩🏾‍🏫</div><div><div class="testi-name">Grace Namukasa</div><div class="testi-role">Teacher · Entebbe</div><div class="testi-stars">★★★★★</div></div></div></div>
    <div class="testi-card"><div class="testi-qm">"</div><div class="testi-text">The industrial listings are detailed and verified. We leased our warehouse through HousingHub and the whole process was seamless.</div><div class="testi-author"><div class="testi-avatar">👨🏾‍🏭</div><div><div class="testi-name">Robert Tumwebaze</div><div class="testi-role">Operations Manager · Jinja</div><div class="testi-stars">★★★★★</div></div></div></div>
  </div>
</section>

<section class="cta-section z reveal">
  <h2 class="cta-big">Your Next<br><em>Space</em> <span class="str">Awaits.</span></h2>
  <p class="cta-sub">Hundreds of verified properties for rent, buying or lease — available today.</p>
  <div class="cta-btns">
    <a href="properties.php?browse=1" class="cta-p" data-label="Let's Go!">Browse All Properties</a>
    <a href="register.php" class="cta-s" data-label="Join!">Create Account</a>
  </div>
</section>

<footer>&copy; 2026 HousingHub | All Rights Reserved</footer>

<?php else: ?>
<!-- ═══ BROWSE / FILTER VIEW ══════════════════════════════════ -->
<div class="browse-wrap z">
  <div class="browse-header">
    <div class="browse-header-inner">
      <div>
        <div class="browse-eyebrow">HousingHub Properties</div>
        <h1 class="browse-title">
          <?php if($currentType): ?>
            <?= htmlspecialchars($currentType) ?> <em>Properties</em>
          <?php elseif($currentSearch): ?>
            Results for <em>"<?= $currentSearch ?>"</em>
          <?php else: ?>
            All <em>Properties</em>
          <?php endif; ?>
        </h1>
        <p class="browse-count">
          <strong><?= mysqli_num_rows($properties) ?></strong>
          <?= mysqli_num_rows($properties) === 1 ? 'property' : 'properties' ?> found
          <?php if($currentType): ?>· filtered by <strong><?= htmlspecialchars($currentType) ?></strong><?php endif; ?>
        </p>
      </div>
      <a href="properties.php" style="font-size:12px;color:var(--muted);text-decoration:none;letter-spacing:1px;transition:color .3s" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--muted)'">← Back to Landing</a>
    </div>
  </div>

  <div class="browse-controls">
    <form class="browse-search-box" method="GET" action="properties.php">
      <?php if($currentType): ?><input type="hidden" name="type" value="<?= htmlspecialchars($currentType) ?>"><?php endif; ?>
      <input type="text" name="search" placeholder="Search by name, address, or type…" value="<?= $currentSearch ?>">
      <button type="submit">Search</button>
    </form>
    <div class="type-filters">
      <a href="properties.php?browse=1<?= $currentSearch?'&search='.urlencode($currentSearch):'' ?>" class="type-pill <?= !$currentType?'active':'' ?>">All</a>
      <a href="properties.php?type=Commercial<?= $currentSearch?'&search='.urlencode($currentSearch):'' ?>" class="type-pill <?= $currentType==='Commercial'?'active':'' ?>"><span class="type-pill-icon">🏢</span>Commercial</a>
      <a href="properties.php?type=Residential<?= $currentSearch?'&search='.urlencode($currentSearch):'' ?>" class="type-pill <?= $currentType==='Residential'?'active':'' ?>"><span class="type-pill-icon">🏠</span>Residential</a>
      <a href="properties.php?type=Industrial<?= $currentSearch?'&search='.urlencode($currentSearch):'' ?>" class="type-pill <?= $currentType==='Industrial'?'active':'' ?>"><span class="type-pill-icon">🏭</span>Industrial</a>
      <a href="properties.php?type=Agricultural<?= $currentSearch?'&search='.urlencode($currentSearch):'' ?>" class="type-pill <?= $currentType==='Agricultural'?'active':'' ?>"><span class="type-pill-icon">🌾</span>Agricultural</a>
      <a href="properties.php?type=Special+Purpose<?= $currentSearch?'&search='.urlencode($currentSearch):'' ?>" class="type-pill <?= $currentType==='Special Purpose'?'active':'' ?>"><span class="type-pill-icon">🏛️</span>Special Purpose</a>
      <a href="properties.php?type=Land<?= $currentSearch?'&search='.urlencode($currentSearch):'' ?>" class="type-pill <?= $currentType==='Land'?'active':'' ?>"><span class="type-pill-icon">🗺️</span>Land</a>
    </div>
  </div>

  <div class="browse-body">
    <div class="prop-grid">
      <?php if(mysqli_num_rows($properties) > 0): ?>
        <?php while($p = mysqli_fetch_assoc($properties)): ?>
        <div class="prop-card">
          <div class="prop-card-top">
            <span class="prop-type-badge"><?= htmlspecialchars($p['property_type']) ?></span>
            <span class="prop-purpose-badge <?= strtolower($p['purpose']) ?>"><?= strtoupper($p['purpose']) ?></span>
          </div>
          <div class="prop-card-body">
            <div class="prop-name"><?= htmlspecialchars($p['property_name']) ?></div>
            <div class="prop-address"><?= htmlspecialchars($p['address']) ?></div>
            <div class="prop-meta">
              <div class="prop-meta-item"><div class="prop-meta-label">Units</div><div class="prop-meta-value"><?= $p['units'] ?? 'N/A' ?></div></div>
              <div class="prop-meta-item"><div class="prop-meta-label">Rooms</div><div class="prop-meta-value"><?= $p['bedrooms'] ?? 'N/A' ?></div></div>
              <div class="prop-meta-item"><div class="prop-meta-label">Size</div><div class="prop-meta-value"><?= $p['size_sqft'] ?> sqft</div></div>
              <div class="prop-meta-item"><div class="prop-meta-label">Type</div><div class="prop-meta-value"><?= $p['property_type'] ?></div></div>
            </div>
          </div>
          <div class="prop-price">
            <div>
              <div class="prop-price-label">Price</div>
              <div class="prop-price-sub"><?= ucfirst(strtolower($p['purpose'])) ?></div>
            </div>
            <div class="prop-price-value">UGX <?= number_format($p['rent_amount'] ?? 0) ?></div>
          </div>
          <?php
          $amenQ = mysqli_query($conn, "SELECT a.name, a.cost_type FROM amenities a JOIN property_amenities pa ON a.id = pa.amenity_id WHERE pa.property_id = " . intval($p['id']));
          $amenities = [];
          while($a = mysqli_fetch_assoc($amenQ)) $amenities[] = $a;
          if(!empty($amenities)): ?>
          <div class="prop-amenities">
            <div class="prop-amenities-label">Amenities</div>
            <div class="prop-amenity-tags">
              <?php foreach(array_slice($amenities,0,5) as $a): ?>
              <span class="prop-amenity-tag"><?= htmlspecialchars($a['name']) ?></span>
              <?php endforeach; ?>
              <?php if(count($amenities)>5): ?><span class="prop-amenity-tag">+<?= count($amenities)-5 ?> more</span><?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
          <div class="prop-card-actions">
            <a href="property_view.php?id=<?= $p['id'] ?>" class="prop-btn view">View</a>
            <?php if($p['purpose']==='Rent'): ?>
            <a href="payment_method.php?property_id=<?= $p['id'] ?>&action=rent" class="prop-btn rent">Rent</a>
            <?php elseif($p['purpose']==='Buy'): ?>
            <a href="payment_method.php?property_id=<?= $p['id'] ?>&action=buy" class="prop-btn buy">Buy</a>
            <?php elseif($p['purpose']==='Lease'): ?>
            <a href="payment_method.php?property_id=<?= $p['id'] ?>&action=lease" class="prop-btn lease">Lease</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-icon">🏠</div>
          <h3>No Properties Found</h3>
          <p>Try a different search term or browse all available properties.</p>
          <a href="properties.php?browse=1">Browse All Properties</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <footer>&copy; 2026 HousingHub | All Rights Reserved</footer>
</div>
<?php endif; ?>

<script>
function closeAllMenus(){document.querySelectorAll('.dd-menu.open').forEach(m=>m.classList.remove('open'));document.querySelectorAll('.dd-btn.open').forEach(b=>b.classList.remove('open'));}
document.querySelectorAll('.dropdown').forEach(dd=>{var btn=dd.querySelector('.dd-btn'),menu=dd.querySelector('.dd-menu');if(!btn||!menu)return;btn.addEventListener('click',e=>{e.stopPropagation();var o=menu.classList.contains('open');closeAllMenus();if(!o){menu.classList.add('open');btn.classList.add('open');}});menu.addEventListener('mousedown',e=>e.stopPropagation());menu.addEventListener('click',e=>e.stopPropagation());});
document.addEventListener('click',closeAllMenus);
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeAllMenus();});

const dot=document.getElementById('cur-dot'),ring=document.getElementById('cur-ring'),trail=document.getElementById('cur-trail'),lbl=document.getElementById('cur-label');
let mx=-200,my=-200,rx=-200,ry=-200,tx=-200,ty=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;dot.style.left=mx+'px';dot.style.top=my+'px';lbl.style.left=(mx+18)+'px';lbl.style.top=(my-10)+'px';});
(function tick(){rx+=(mx-rx)*.15;ry+=(my-ry)*.15;tx+=(mx-tx)*.06;ty+=(my-ty)*.06;ring.style.left=rx+'px';ring.style.top=ry+'px';trail.style.left=tx+'px';trail.style.top=ty+'px';requestAnimationFrame(tick);})();
document.querySelectorAll('a,button,input,select,.cat-tile,.showcase-card,.feat-card,.how-step,.testi-card,.prop-card').forEach(el=>{
  el.addEventListener('mouseenter',()=>{document.body.classList.add('cursor-hover');const l=el.getAttribute('data-label');if(l){lbl.textContent=l;lbl.classList.add('visible');}});
  el.addEventListener('mouseleave',()=>{document.body.classList.remove('cursor-hover');lbl.classList.remove('visible');});
});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));

function count(id,target,suffix=''){const el=document.getElementById(id);if(!el)return;let n=0;const step=target/(1800/16);const t=setInterval(()=>{n+=step;if(n>=target){n=target;clearInterval(t);}el.textContent=Math.floor(n)+suffix;},16);}
setTimeout(()=>{count('sn1',500,'+');count('sn2',6);count('sn3',40,'+');count('sn4',1200,'+');},800);

const ro=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');ro.unobserve(e.target);}});},{threshold:.12});
document.querySelectorAll('.reveal').forEach(el=>ro.observe(el));

for(let i=0;i<18;i++){const p=document.createElement('div');p.classList.add('ptcl');const sz=Math.random()*3+1;p.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:rgba(200,164,60,${(Math.random()*.5+.2).toFixed(2)});animation-duration:${Math.random()*20+12}s;animation-delay:${Math.random()*15}s;`;document.body.appendChild(p);}
</script>
</body>
</html>