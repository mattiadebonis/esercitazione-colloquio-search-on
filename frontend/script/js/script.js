//VARIABLES
let mainElement = document.getElementsByTagName("MAIN")[0];
let loginForm = "";
let mailInput = "";
let pwdInput = "";
let loginButton = "";
let logoutButton = "";
let exchangeButton = "";
let logoutContainer = "";
let scanContainer = "";
let contactIdInput = "";
let loginMessage = "";
var isLogged = false;
var loginStatus

//FUNCTIONS
function setAttributes(el, attrs) {
    //helper function
    for (var key in attrs) {
       el.setAttribute(key, attrs[key]);
    }
}

function setLoginPageSetting(login){
    //Login setup
    
    //container logut
    logoutContainer = document.createElement("div");
    logoutContainer.setAttribute("id", "logout-container");
    
    //Benvenuto
    var welcome = document.createElement('H3');
    welcome.innerHTML = "Benvenuto/a " + loginStatus.name;
    
    //bottone logout
    logoutButton = document.createElement("BUTTON");   
    logoutButton.innerHTML = "Esci";    
    setAttributes(logoutButton, {"class": "button", "id": "logout-button"});
    
    // Scambia container
    scanContainer = document.createElement("div");
    scanContainer.setAttribute("id", "scan-container");   
    
    //inserisci id contatto
    var scanTitle = document.createElement('LABEL');
    scanTitle.innerHTML = "Inserisci il codice del contatto";
    
    //inserisci id contatto
    var contactIdInput = document.createElement("INPUT"); 
    contactIdInput.innerHTML = "Inserisci l'id del tuo contatto";       
    contactIdInput.setAttribute("type", "text");
    
    //scan button
    exchangeButton = document.createElement("BUTTON");   
    exchangeButton.innerHTML = "Scambia mail";  
    setAttributes(exchangeButton, {"class": "button", "id": "scan-button"});
    
    //login button
    var result = document.createElement('P');
    result.setAttribute("id", "result");
    
    //componi pagina logout
    mainElement.appendChild(logoutContainer);  
    logoutContainer.append(welcome, logoutButton, scanContainer)
    scanContainer.append(scanTitle,contactIdInput,exchangeButton,result);    

    //rimuovi form login    
    loginForm.remove();
    loginForm = "";
    logoutButton.addEventListener("click",function(){    
        requestAuth("", "")
    });   

    exchangeButton.addEventListener("click",function(){        
        exchangeMail(contactIdInput.value, loginStatus.user_id);   
    });
    
}

function setLogoutPageSetting(login){
    //Logout setup
    if(!loginForm){
        loginForm = document.createElement("form");
        setAttributes(loginForm, {"id":"form-login", "method": "POST"});

        var loginTitle = document.createElement("H3"); 
        loginTitle.innerHTML = "Esegui il Login";       

        //mail
        mailInput = document.createElement("INPUT"); 
        mailInput.innerHTML = "Inserisci la mail";       
        mailInput.setAttribute("type", "text");

        //password
        pwdInput = document.createElement("INPUT");
        pwdInput.innerHTML = "Inserisci password";
        setAttributes(pwdInput, {"type": "password", "id": "pwd"});
        
        //crea bottone login
        loginButton = document.createElement("BUTTON");   
        loginButton.innerHTML = "Login";  
        setAttributes(loginButton, {"type":"submit", "class": "button", "id": "login-button"});
        
        loginForm.append(loginTitle, mailInput, pwdInput, loginButton); 
        mainElement.appendChild(loginForm);   

        if(logoutContainer != ""){
            logoutContainer.remove();
        }
    }

    //messaggio errore login
    if (login && login.success == 0){
        loginMessage = document.createElement("P"); 
        loginMessage.innerHTML = login.message;
        loginForm.appendChild(loginMessage);
    }

    loginForm.addEventListener('submit', event => {
        if(loginMessage){
            loginMessage.remove();
        }
        requestAuth(mailInput.value, pwdInput.value)
        event.preventDefault()
      })
}

function requestAuth(username, password){ 
    //Api Auth
    fetch("http://localhost:10533/login.php",
        {
            method: "POST",
            body:JSON.stringify({
                "email": username,
                "password": password
            })
        })
        .then(res => res.json())
        .then(data =>{
            if(data.success == 1){
                loginStatus = data;
                setLoginPageSetting();

            }else if(data.success == 0){
                setLogoutPageSetting(data);

            }
        })
        .catch(function(res){ 
            console.log("Errore di connessione al server")
        });  
}

function exchangeMail(contact_id, user_id){
   //Exchange mail
   fetch("http://localhost:10533/exchange-mail.php",
        {
            method: "POST",
            body:JSON.stringify({
                'user_id': user_id,
                "contact_id": contact_id,
            })
        })
        .then(res => res.json())
        .then(data =>{
            element = document.getElementById("result")
            element.innerHTML = data.message
        })
        .catch(function(res){ console.log("Errore di connessione al server")});    
}

//EVENT
window.onload = (event) => {
    if (!loginStatus){
        setLogoutPageSetting();
    }else{
        setLoginPageSetting();
    }
};
