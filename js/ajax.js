const tools = {
    loader: (load) => {
        switch (load) {
            case '/login':
                onLoad.loginPage()
                break;
            /*case '/register':
                onLoad.registerPage()
                break;*/
            default:
                onLoad.loginPage();
        }
    },
}
  const onLoad = {
      loginPage: ()=> {
          $('#loginForm').on('submit', async function(e) {
              e.preventDefault();

              const username = $('#username').val();
              const password = $('#password').val();
              const message = $('#message').val();

              const data = {
                  username: username,
                  password: password
              };

              try {
                  const response = await fetch('api/login', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                      },
                      body: JSON.stringify(data),
                      credentials: 'include'
                  });

                  if (response.ok) {
                      const result = await response.json();
                      console.log('Login success:', result);

                      const urlParams = new URLSearchParams(window.location.search);
                      const redirectUrl = urlParams.get('from');
                      window.location.href = redirectUrl;
                  } else {
                      const error = await response.json();
                      alert('Login failed: ' + error.message);
                  }

              } catch (err) {
                  message.textContent = 'Ошибка соединения с сервером';
              }
          });
     },
  }

$(document).ready( function() {

  tools.loader(window.location.pathname);
  
})

window.addEventListener("popstate", function(e) {

  tools.loader(window.location.pathname);

  });


