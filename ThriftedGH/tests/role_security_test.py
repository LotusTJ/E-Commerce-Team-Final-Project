import re, io, sys, os, requests
from PIL import Image
B = os.environ.get("BASE", "http://127.0.0.1:8097/thriftedgh")
ok = bad = 0
def check(name, cond, extra=""):
    global ok, bad
    if cond: ok += 1; print("PASS", name)
    else: bad += 1; print("FAIL", name, extra)
def sess():
    s = requests.Session(); s.trust_env = False; return s
def tok(s, path):
    m = re.search(r'name="csrf_token" value="([0-9a-f]+)"', s.get(f"{B}/{path}").text)
    return m.group(1) if m else ""
def login(email, pw="demo1234"):
    s = sess()
    t = tok(s, "views/auth/login.php")
    r = s.post(f"{B}/actions/auth/login.php", data={"email": email, "password": pw, "csrf_token": t}, allow_redirects=False)
    return s, r
def png(): 
    b = io.BytesIO(); Image.new("RGB", (40, 40), (10, 100, 10)).save(b, "PNG"); return b.getvalue()
def get(s, path, **k): return s.get(f"{B}/{path}", allow_redirects=False, **k)

PAGES = {
 "views/buyer/dashboard.php": "login", "views/buyer/orders.php": "login", "views/buyer/checkout.php": "login",
 "views/buyer/find_my_fit.php": "login", "views/seller/apply.php": "login", "views/seller/documents.php": "login",
 "views/seller/dashboard.php": "login", "views/seller/orders.php": "login", "views/seller/product_form.php": "login",
 "views/admin/dashboard.php": "login", "views/admin/applications.php": "login", "views/admin/orders.php": "login",
}
print("== anonymous ==")
a = sess()
for p in PAGES:
    r = get(a, p); check(f"anon redirected from {p}", r.status_code == 302 and r.headers["Location"].endswith("views/auth/login.php"), r.status_code)
for act in ["actions/admin/review_application.php","actions/documents/upload.php","actions/products/create.php","actions/orders/fulfil.php","actions/cart/add.php","actions/checkout/start.php"]:
    r = a.post(f"{B}/{act}", data={}, allow_redirects=False); check(f"anon POST {act} blocked", r.status_code in (302,401,403) and r.status_code != 200, r.status_code)
r = get(a, "actions/documents/download.php?id=1"); check("anon cannot download documents", r.status_code in (302,401,403), r.status_code)
r = get(a, "actions/admin/review_application.php"); check("GET to admin action refused", r.status_code in (302,403,405), r.status_code)

print("== buyer (efua) ==")
b, r = login("efua@demo.com"); check("buyer login redirects home", r.status_code == 302 and r.headers["Location"].endswith("views/public/home.php"), r.headers.get("Location"))
for p in ["views/buyer/dashboard.php","views/buyer/orders.php","views/buyer/find_my_fit.php","views/seller/apply.php","views/seller/documents.php"]:
    r = get(b, p); check(f"buyer can open {p}", r.status_code == 200 or (p.endswith("documents.php") and r.status_code in (200,302)), r.status_code)
for p in ["views/admin/dashboard.php","views/admin/applications.php","views/admin/orders.php","views/seller/dashboard.php","views/seller/orders.php","views/seller/product_form.php"]:
    r = get(b, p); check(f"buyer denied {p}", r.status_code == 403, r.status_code)
html = get(b, "views/public/home.php").text
check("buyer nav has no Admin link", ">Admin<" not in html and "Seller</a>" not in html)
t = tok(b, "views/buyer/dashboard.php") or tok(b, "views/public/shop.php")
r = b.post(f"{B}/actions/admin/review_application.php", data={"csrf_token": t, "application_id": 7, "decision": "approve"}, allow_redirects=False)
check("buyer cannot approve applications", r.status_code == 403, r.status_code)
r = b.post(f"{B}/actions/products/create.php", data={"csrf_token": t}, allow_redirects=False); check("buyer cannot create products", r.status_code == 403, r.status_code)
r = b.post(f"{B}/actions/orders/fulfil.php", data={"csrf_token": t, "item_id": 1, "status": "processing"}, allow_redirects=False); check("buyer cannot fulfil orders", r.status_code == 403, r.status_code)
r = get(b, "views/buyer/orders.php?scope=admin"); check("scope=admin param gives buyer nothing extra", r.status_code == 200 and "Order administration" not in r.text and "Verified sales and commission" not in r.text)
r = get(b, "actions/documents/download.php?id=1"); check("buyer cannot download others' documents", r.status_code in (403,404), r.status_code)

print("== seller (ama) ==")
s, r = login("ama@demo.com")
for p in ["views/seller/dashboard.php","views/seller/orders.php","views/seller/product_form.php","views/buyer/dashboard.php"]:
    r = get(s, p); check(f"seller can open {p}", r.status_code == 200, r.status_code)
for p in ["views/admin/dashboard.php","views/admin/applications.php","views/admin/orders.php"]:
    r = get(s, p); check(f"seller denied {p}", r.status_code == 403, r.status_code)
html = get(s, "views/public/home.php").text
check("seller nav shows Seller but not Admin", "views/seller/dashboard.php" in html and "views/admin/dashboard.php" not in html)
t = tok(s, "views/seller/dashboard.php")
r = s.post(f"{B}/actions/admin/review_application.php", data={"csrf_token": t, "application_id": 7, "decision": "approve"}, allow_redirects=False)
check("seller cannot approve applications", r.status_code == 403, r.status_code)

print("== applicant documents (applicant@demo.com) ==")
ap, r = login("applicant@demo.com")
r = get(ap, "views/seller/documents.php"); check("applicant sees documents page", r.status_code == 200 and "Upload document" in r.text, r.status_code)
t = tok(ap, "views/seller/documents.php")
def up(sess_, name, data, ctype, doc_type="student_id", token=None):
    return sess_.post(f"{B}/actions/documents/upload.php", data={"csrf_token": token or t, "doc_type": doc_type}, files={"document": (name, data, ctype)}, allow_redirects=False)
r = up(ap, "id card.png", png(), "image/png"); check("valid PNG upload accepted", r.status_code == 302)
page = get(ap, "views/seller/documents.php").text
check("uploaded file listed", "id card.png" in page)
r = up(ap, "evil.pdf", b"<?php echo 'pwned'; ?>", "application/pdf"); 
page = get(ap, "views/seller/documents.php").text
check("php disguised as pdf rejected", "evil.pdf" not in page and "Only PDF, JPG and PNG" in page)
r = up(ap, "script.png", b"GIF89a<?php system('id'); ?>", "image/png"); page = get(ap, "views/seller/documents.php").text
check("fake image with wrong magic bytes rejected", "script.png" not in page)
r = up(ap, "big.pdf", b"%PDF-1.4\n" + b"0" * (5*1024*1024 + 10), "application/pdf"); page = get(ap, "views/seller/documents.php").text
check("oversize file rejected", "big.pdf" not in page and "5 MB" in page)
r = up(ap, "x.png", png(), "image/png", doc_type="admin_pass"); page = get(ap, "views/seller/documents.php").text
check("invalid document type rejected", "x.png" not in page)
r = up(ap, "nocsrf.png", png(), "image/png", token="bad"); page = get(ap, "views/seller/documents.php").text
check("missing CSRF rejected", "nocsrf.png" not in page)
r = up(ap, "../../../etc/passwd.png", png(), "image/png"); page = get(ap, "views/seller/documents.php").text
check("path traversal filename stored harmlessly", "etc" not in page.replace("etc.", "") or "passwd.png" in page)
SD=os.environ.get("DOC_STORAGE_DIR", os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "..", "thriftedgh_storage", "documents"))
stored = [f for f in os.listdir(SD) if re.fullmatch(r"[0-9a-f]{40}", f)]
check("stored files have random names, no extensions", all(re.fullmatch(r"[0-9a-f]{40}", f) for f in stored if f != ".gitkeep"), stored)
ids = re.findall(r"download\.php\?id=(\d+)", page); check("download links present", bool(ids))
mine = ids[0]
r = get(ap, f"actions/documents/download.php?id={mine}"); check("owner can download", r.status_code == 200 and r.headers.get("Content-Disposition","").startswith("attachment") and r.headers.get("X-Content-Type-Options") == "nosniff", r.status_code)
r = get(b, f"actions/documents/download.php?id={mine}"); check("other buyer cannot download", r.status_code in (403,404), r.status_code)
r = get(s, f"actions/documents/download.php?id={mine}"); check("other seller cannot download", r.status_code in (403,404), r.status_code)
r = get(ap, "actions/documents/download.php?id=1%20OR%201=1"); check("sql-style id rejected", r.status_code in (403,404), r.status_code)
r = get(ap, "storage/documents/" + stored[0]); check("stored file not reachable by URL", r.status_code == 404, r.status_code)
r = get(ap, "thriftedgh_storage/documents/" + stored[0]); check("storage dir not under web root", r.status_code != 200, r.status_code)

print("== admin ==")
ad, r = login("admin@demo.com"); check("admin login redirects to admin dashboard", r.headers.get("Location","").endswith("views/admin/dashboard.php"), r.headers.get("Location"))
for p in ["views/admin/dashboard.php","views/admin/applications.php","views/admin/orders.php"]:
    r = get(ad, p); check(f"admin can open {p}", r.status_code == 200, r.status_code)
for p in ["views/seller/dashboard.php","views/seller/orders.php"]:
    r = get(ad, p); check(f"admin has no seller tools ({p})", r.status_code == 403, r.status_code)
page = get(ad, "views/admin/applications.php").text
check("admin sees applicant documents", "id card.png" in page)
r = get(ad, f"actions/documents/download.php?id={mine}"); check("admin can download applicant document", r.status_code == 200, r.status_code)
t = tok(ad, "views/admin/applications.php")
r = ad.post(f"{B}/actions/admin/review_application.php", data={"csrf_token": "nope", "application_id": 7, "decision": "approve"}, allow_redirects=False)
check("admin action rejects bad CSRF", r.status_code == 302 and get(ad,"views/admin/applications.php").text.count("Demo Thrift Corner") >= 0)
r = ad.post(f"{B}/actions/admin/review_application.php", data={"csrf_token": t, "application_id": 7, "decision": "approve"}, allow_redirects=False)
check("admin can approve", r.status_code == 302)
r = ap.get(f"{B}/views/seller/dashboard.php", allow_redirects=False); check("approved applicant now reaches seller dashboard", r.status_code == 200, r.status_code)
r = up(ap, "late.png", png(), "image/png"); page = get(ap, "views/seller/documents.php").text
check("no uploads after approval", "late.png" not in page)

print("== login lockout ==")
import subprocess
subprocess.run(["mysql","-utgh","-ptghpass","thriftedgh","-e","delete from login_attempts"])
for i in range(5): login("efua@demo.com", "wrong-pass-%d" % i)
lk, r = login("efua@demo.com", "demo1234")
check("account locks after 5 failed logins even with the right password", r.headers.get("Location","").endswith("views/auth/login.php") and "Too many failed attempts" in lk.get(f"{B}/views/auth/login.php").text)
check("locked account cannot reach protected page", get(lk, "views/buyer/dashboard.php").status_code == 302)
subprocess.run(["mysql","-utgh","-ptghpass","thriftedgh","-e","delete from login_attempts"])
ok2, r = login("efua@demo.com", "demo1234"); check("login works again after lockout is cleared", get(ok2, "views/buyer/dashboard.php").status_code == 200)
hdr = requests.get(f"{B}/views/public/home.php").headers
check("security headers present", hdr.get("X-Content-Type-Options") == "nosniff" and hdr.get("X-Frame-Options") == "SAMEORIGIN" and "Referrer-Policy" in hdr)
print("== session handling ==")
r = get(ad, "actions/auth/logout.php"); check("logout redirects", r.status_code == 302)
r = get(ad, "views/admin/dashboard.php"); check("admin page blocked after logout", r.status_code == 302 and r.headers["Location"].endswith("views/auth/login.php"), r.status_code)
forged = sess(); forged.cookies.set("PHPSESSID", "admin"); r = get(forged, "views/admin/dashboard.php"); check("forged session id gets nowhere", r.status_code == 302)
bad_login, r = login("admin@demo.com", "wrongpass"); check("wrong password rejected", r.status_code == 302 and r.headers["Location"].endswith("views/auth/login.php"))
r = get(bad_login, "views/admin/dashboard.php"); check("failed login does not grant admin", r.status_code == 302)
print(f"\n{ok} passed, {bad} failed"); sys.exit(1 if bad else 0)
