var size = 0;
var reader = new FileReader();
var mp3data = null;
reader.onload = readBinary;

function handleFileSelect(evt) {
    var files = evt.dataTransfer.files;
    size = files[0].size;
    reader.readAsBinaryString(files[0]);
}

function readBinary(f) {
    document.getElementById('link').style.display = 'none';

    error = false;
    r = reader.result;
    datasize = 0;
    fileoffset = 13;
    readed = 13;
    mp3data = null;

    while ((size - fileoffset) > datasize) {
        binary_data = r.substr(readed, 12);
        readed += 12;

        data = unpack("C1TagType/" + "C3DataSize/" + "C3TimeStamp/" + "C1TimeStampEx/" + "C3StreamId/" + "C1MediaInfo", binary_data);
        datasize = (data['DataSize1'] << 16) | (data['DataSize2'] << 8) | (data['DataSize3']);

        binary_data = r.substr(readed, datasize - 1);
        readed += (datasize + 3);

        if (data['TagType'] == 8) {

            bodyInfo = unpack('Cflags', binary_data);
            c = (bodyInfo['flags'] & 0xF0) >> 4;
            if (c !== 2 && c !== 14 & c !== 15) {
                alert('404 - No Mp3 audio found! :( ');
                error = true;
                break;
            }

            if (mp3data == null){
				mp3data = binary_data;
			}else{
				mp3data = mp3data + binary_data;
			}

        }
        fileoffset += 15 + datasize;
    }

    if (!error) {
        // converting to Binary Array
        var mp3Bytesdata = new Uint8Array(mp3data.length);
        for (var i = 0; i < mp3data.length; i++) {
			mp3Bytesdata[i] = mp3data.charCodeAt(i);
		}

        //mp3data = window.btoa(mp3data); // in case I need use this data as bs64 for something
        downdata = new Blob([mp3Bytesdata], {
            type: 'audio/mpeg'
        });
        document.getElementById('downlink').href = window.URL.createObjectURL(downdata);
        document.getElementById('link').style.display = 'block';
    }
}