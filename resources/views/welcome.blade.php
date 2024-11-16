<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel</title>
        <script src="https://cdn.jsdelivr.net/npm/axios@1.7.5/dist/axios.min.js"></script>
    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50">
        <script>
            const payload = {
                grant_type:"password",
                response_type:"token",
                username:"{{env('QT_USER_NAME')}}",
                password:"{{env('QT_PASSWORD')}}"
            };
            axios.post("https://{{ env('QT_END_POINT') }}/v1/auth/token",payload)
            .then((response)=>{
                console.log(response);
            }).catch((error)=>{
                console.log(error);
            })
        </script>
    </body>
</html>
