<style>
    :root{color-scheme:light;--ink:#112040;--subtle:#516079;--paper:#f6f5f1;--white:#fff;--line:#dce1e9;--brand:#172f63;--gold:#f1b83e;--danger:#a42b35}
    *{box-sizing:border-box}
    body{margin:0;background:var(--paper);color:var(--ink);font:15px/1.55 system-ui,sans-serif}
    a{color:var(--brand);font-weight:650;text-underline-offset:.2em}
    :focus-visible{outline:3px solid #95680a;outline-offset:3px}
    h1,h2,h3,p{margin:0 0 .65em}
    h1{font-size:clamp(1.65rem,5vw,2.4rem);letter-spacing:-.04em;line-height:1.15}
    h2{font-size:1.25rem;letter-spacing:-.02em}
    img{max-width:100%;height:auto}
    .skip{position:absolute;top:-100vh;left:1rem;z-index:5;background:var(--white);padding:.7rem 1rem}
    .skip:focus{top:.5rem}
    .shell{min-height:100vh;display:grid;grid-template-columns:15rem minmax(0,1fr)}
    .sidebar{padding:1.6rem 1rem;background:#10234a;color:#e9eefb}
    .sidebar a{color:inherit;text-decoration:none}
    .brand{display:flex;gap:.8rem;align-items:center;padding:.45rem .35rem 1.4rem;color:inherit;text-decoration:none;font-size:1.1rem;font-weight:760;line-height:1.2}
    .brand img{width:2.3rem;border-radius:50%;background:#fff}
    .brand small{display:block;margin-top:.2rem;color:#bdc9eb;font-weight:550;font-size:.75rem}
    .sidebar nav{display:grid;gap:.25rem}
    .sidebar nav a{padding:.56rem .72rem;border-radius:.45rem;font-size:.9rem;font-weight:600}
    .sidebar nav a:hover,.sidebar nav a[aria-current="page"]{background:#e5ebff;color:#10234a}
    .main{min-width:0}
    .topbar{display:flex;justify-content:flex-end;align-items:center;gap:.9rem;background:var(--white);border-bottom:1px solid var(--line);padding:.72rem clamp(1rem,4vw,2.5rem)}
    .topbar a{margin-inline-end:auto}
    .wrap{max-width:79rem;margin-inline:auto;padding:clamp(1rem,4vw,2.5rem)}
    .page-head{display:flex;gap:1rem;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem}
    .page-head h1{margin-bottom:0}
    .breadcrumbs{display:inline-block;margin-bottom:.7rem;font-size:.87rem}
    .subtle{color:var(--subtle)}
    .panel,.metric,.login{background:var(--white);border:1px solid var(--line);border-radius:.8rem;padding:1.2rem;box-shadow:0 2px 5px #16234a06}
    .metric-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(9.5rem,1fr));gap:1rem;margin-bottom:1.4rem}
    .metric strong{display:block;font-size:1.9rem;line-height:1.2}
    .toolbar,.actions{display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;margin-bottom:.9rem}
    .actions:last-child{margin-bottom:0}
    .spread{justify-content:space-between}
    .button,button{font:inherit;font-weight:700;background:var(--brand);color:#fff;border:1px solid var(--brand);border-radius:.43rem;padding:.55rem .85rem;cursor:pointer;text-decoration:none;line-height:1.35;display:inline-flex;align-items:center;min-height:2.35rem}
    .button.ghost,button.ghost{background:var(--white);color:var(--brand);border-color:#b8c3d7}
    .button.danger,button.danger{background:var(--white);border-color:#ddb6bb;color:var(--danger)}
    .button:hover,button:hover{filter:brightness(.92)}
    .status,.errors{padding:.7rem 1rem;border:1px solid #91b6a5;background:#e8f4ed;border-radius:.45rem;margin-bottom:1rem}
    .errors{border-color:#e4a8ab;background:#fff1f1;color:#78232c}
    .errors ul{margin:.3rem 0 0;padding-left:1.1rem}
    .table-scroll{overflow-x:auto}
    table{border-collapse:collapse;width:100%;text-align:left}
    th,td{padding:.7rem .65rem;border-bottom:1px solid var(--line);vertical-align:top}
    th{font-size:.8rem;color:var(--subtle)}
    tbody tr:last-child td{border-bottom:0}
    .pill{display:inline-block;color:#183064;background:#eaf0fd;padding:.1rem .5rem;border-radius:1rem;white-space:nowrap;font-size:.81rem}
    .pager{margin-top:1rem;display:flex;justify-content:space-between;align-items:center;gap:.7rem}
    .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
    .field{display:grid;gap:.35rem;align-content:start;margin-bottom:.6rem}
    .field>label,.label,legend{font-weight:690}
    .field small,.editor-note{color:var(--subtle)}
    .full{grid-column:1/-1}
    input:not([type=checkbox]),select,textarea,.editor-content{width:100%;min-width:0;background:#fff;color:var(--ink);border:1px solid #b8c3d7;border-radius:.42rem;min-height:2.55rem;padding:.52rem .68rem;font:inherit}
    textarea{resize:vertical}
    input[type=checkbox]{accent-color:var(--brand)}
    .textarea-html{font-family:ui-monospace,monospace;font-size:.95rem}
    .editor-content{min-height:15rem;overflow-wrap:anywhere}
    .editor-content img{max-height:16rem;object-fit:contain}
    .editor-content table{width:auto}
    .editor-content figure{margin:1rem 0}
    .editor-note{margin:.3rem 0 .55rem;font-size:.85rem}
    fieldset{min-width:0;border:1px solid var(--line);border-radius:.5rem;padding:.8rem;margin:0 0 1rem}
    summary{cursor:pointer;font-weight:680}
    dl{display:grid;grid-template-columns:minmax(7rem,auto) 1fr;gap:.6rem 1rem;overflow-wrap:anywhere}
    dt{font-weight:700}
    dd{margin:0}
    .body-text{white-space:pre-wrap;overflow-wrap:anywhere}
    .preview-image{max-width:16rem;border-radius:.4rem}
    .login{max-width:29rem;margin:clamp(4rem,11vh,9rem) auto 0;padding:clamp(1rem,5vw,2rem)}
    .login-wrap{padding:0 1rem}
    .login .field{margin-bottom:1rem}
    @media(max-width:730px){.shell{display:block}.sidebar{padding:.8rem 1rem}.sidebar nav{display:flex;overflow-x:auto}.sidebar nav a{white-space:nowrap}.brand{padding:0 0 .55rem}.topbar{padding:.5rem 1rem}.topbar .person{display:none}.page-head{flex-wrap:wrap}.form-grid{grid-template-columns:1fr}.panel{padding:.85rem}.table-scroll{margin-inline:-.15rem}}
</style>
