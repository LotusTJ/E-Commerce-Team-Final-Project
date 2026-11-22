import urllib.request,urllib.error,urllib.parse,http.cookiejar,re,os
if os.environ.get('COMMERCE_HTTP_TESTS')!='disposable': raise SystemExit('Run only against the disposable commerce test server with COMMERCE_HTTP_TESTS=disposable.')
base=os.environ.get('COMMERCE_TEST_URL','http://127.0.0.1:8765/ThriftedGH/')
def client():return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
def get(c,path,data=None):
 try:
  with c.open(base+path,urllib.parse.urlencode(data).encode() if data is not None else None) as r:return r.status,r.read().decode()
 except urllib.error.HTTPError as e:return e.code,e.read().decode()
def check(v,label):
 assert v,label
 print('PASS: '+label)
def login(email):
 portal='admin_' if email.startswith('admin@') else ('seller_' if email.startswith('a@') else '')
 c=client();status,page=get(c,'views/auth/'+portal+'login.php');csrf=re.search(r'name="csrf_token" value="([a-f0-9]+)"',page).group(1)
 status,body=get(c,'actions/'+portal+'login_action.php',{'email':email,'password':'Member4-test-fixture','csrf_token':csrf})
 status,body=get(c,'views/public/home.php')
 check(status==200 and 'Sign out' in body,'login '+email)
 return c
anon=client();status,body=get(anon,'views/buyer/cart.php');check(status==200 and 'Sign in' in body,'anonymous cart prompts login')
check(get(anon,'actions/add_to_cart_action.php')[0]==405,'cart mutations reject GET')
check(get(anon,'payments/webhook.php')[0]==405,'webhook rejects GET')
check(get(anon,'payments/webhook.php',{'event':'charge.success'})[0]==401,'unsigned webhook rejected')
a=login('buyer@test.invalid');b=login('other@test.invalid');seller=login('a@test.invalid');admin=login('admin@test.invalid')
status,body=get(a,'views/buyer/cart.php');token=re.search(r'name="csrf_token" value="([a-f0-9]+)"',body).group(1)
check(get(a,'actions/add_to_cart_action.php',{'product_id':7})[0]==403,'cart rejects missing CSRF')
check(get(a,'actions/add_to_cart_action.php',{'product_id':7,'csrf_token':'bad'})[0]==403,'cart rejects incorrect CSRF')
status,body=get(a,'actions/add_to_cart_action.php',{'product_id':7,'csrf_token':token});check(status==200 and 'Test 7' in body,'cart form adds real product')
status,body=get(a,'actions/remove_from_cart_action.php',{'product_id':8,'csrf_token':token});check(status==200,'remove unavailable cart item')
status,body=get(a,'views/buyer/checkout.php');check(status==200 and 'Delivery address' in body,'checkout form renders')
status,body=get(a,'views/buyer/orders.php?id=1');check(status==200 and 'Test 1' in body and 'Test 2' in body,'buyer order details render purchased items')
ref=re.search(r'TGH-[a-f0-9]{32}',body).group(0)
status,result=get(a,'views/buyer/payment_result.php?reference='+ref);check(status==200 and 'Payment successful' in result,'verified payment result page renders success')
check(get(b,'views/buyer/payment_result.php?reference='+ref)[0]==404,'payment result belongs only to purchasing buyer')
check(get(b,'views/buyer/orders.php?id=1')[0]==404,'other buyer detail blocked over HTTP')
check(get(b,'views/admin/orders.php')[0]==403,'admin page role check over HTTP')
status,body=get(seller,'views/seller/orders.php?id=1');check(status==200 and 'Test 1' in body and 'Test 2' not in body,'seller page hides other seller item')
status,body=get(admin,'views/admin/orders.php');check(status==200 and '20.50' not in body and '8.50' in body and '12.00' in body,'admin reconciliation shows per seller commission')
for path in ['views/public/shop.php','views/public/home.php','views/public/product.php?id=7','views/buyer/orders.php','views/buyer/cart.php']:
 status,body=get(a,path);check(status==200 and 'Fatal error' not in body and 'Warning:' not in body,path+' renders without PHP warnings')
print('All HTTP checks passed.')
