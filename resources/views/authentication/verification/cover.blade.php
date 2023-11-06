@extends('user_type.auth', ['parentFolder' => 'authentication', 'childFolder' => 'verification', 'hasFooter' => 'footer', 'navbar' => 'cover'])

@section('content')
<!--navbar-->
  <main class="main-content main-content-bg mt-0">
    <section>
      <div class="page-header min-vh-75">
        <div class="container">
          <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
              <div class="card card-plain">
                <div class="card-body px-lg-5 py-lg-5 text-center">
                  <div class="text-center text-muted mb-4">
                    <h2>2-Step Verification</h2>
                  </div>
                  <div class="row gx-2 gx-sm-3">
                    <div class="col">
                      <div class="form-group">
                        <input type="text" class="form-control form-control-lg" maxlength="1" autocomplete="off" autocapitalize="off">
                      </div>
                    </div>
                    <div class="col">
                      <div class="form-group">
                        <input type="text" class="form-control form-control-lg" maxlength="1" autocomplete="off" autocapitalize="off">
                      </div>
                    </div>
                    <div class="col">
                      <div class="form-group">
                        <input type="text" class="form-control form-control-lg" maxlength="1" autocomplete="off" autocapitalize="off">
                      </div>
                    </div>
                    <div class="col">
                      <div class="form-group">
                        <input type="text" class="form-control form-control-lg" maxlength="1" autocomplete="off" autocapitalize="off">
                      </div>
                    </div>
                  </div>
                  <div class="text-center">
                    <button type="button" class="btn bg-gradient-warning w-100">Send code</button>
                    <span class="text-muted text-sm">Haven't received it?<a href="javascript:;"> Resend a new code</a>.</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('assets/img/curved-images/curved9.jpg')"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
<!--footer socials-->
@endsection