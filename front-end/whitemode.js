let whitemode = localStorage.get('whitemode');
const themeswitch = document.getElementById('theme-switch');

const enableWhitemode = () => {
    document.body.classList.add('whitemode');
    localStorage.setitem('whitemode', 'active');
};

const disableWhitemode = () => {
    document.body.classList.remove('whitemode');
    localStorage.setitem('whitemode', null);
};

if(whitemode === "active") enableWhitemode()

themeswitch.addEventListener("click", () => {
  whitemode = localStorage.getitem('whitemode')
  whitemode !== "active" ? enableWhitemode() : disableWhitemode();
});