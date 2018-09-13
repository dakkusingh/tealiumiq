var utag_data = drupalSettings.tealiumiq.tealiumiq.utagData;

(function(a,b,c,d){
    a=drupalSettings.tealiumiq.tealiumiq.utagurl;
    b=document;
    c='script';
    d=b.createElement(c);
    d.src=a;
    d.type='text/javascript';
    d.async=drupalSettings.tealiumiq.tealiumiq.async;
    a=b.getElementsByTagName(c)[0];
    a.parentNode.insertBefore(d,a);
})();