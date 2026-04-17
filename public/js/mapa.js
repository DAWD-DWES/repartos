let map;

async function loadMapScenario() {
    const data = $("#miMapa").data();

    const position = {
        lat: Number(data.lat),
        lng: Number(data.lon)
    };

    const {Map} = await google.maps.importLibrary("maps");
    const {AdvancedMarkerElement} = await google.maps.importLibrary("marker");

    map = new Map(document.getElementById("miMapa"), {
        center: position,
        zoom: 17,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapId: "DEMO_MAP_ID"
    });

    new AdvancedMarkerElement({
        map,
        position
    });
}

window.loadMapScenario = loadMapScenario;



