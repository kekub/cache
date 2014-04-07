Cache
=====

Cache is a *smart* and *intuitive* cache class written in PHP. It stores results as *native PHP code* and makes use of lambda functions to keep your code small.

Usage
-----

### Include

You need to include one file to make use of the cache.

    include 'kevinkub\Cache\Cache.php';
    use \kevinkub\Cache\Cache as Cache;


### Setup

It is required to setup a cache folder for the file cache. This folder should not
be shared with other scripts, that use a file cache.

    Cache::setCacheFolder('cache/phpcache');


### Caching Items

Caching items is done by calling the static method `store()`. The first parameter
is a key used to store the results. Second parameter is a lambda function which
returns the value to be cached. The return needs to be [exportable](http://php.net/var_export).
Third parameter is *optional* and needs to be parseable by [`strtotime()`](http://php.net/strtotime).

    $ifconfig = Cache::store('ifconfig', function(){
        $jsonContent = file_get_contents('http://ifconfig.me/all.json');
        $jsonArray = json_decode($jsonContent, true);
    }, '10 min');

    var_dump($ifconfig);

The above code will load the json data only once in ten minutes. When it is called another time the json data will be loaded from cache.


### Cleaning the cache

The cache does not implement automatic cache cleaning for performance reasons.
If you store want to cache a lot of files or just some rarely accessed big files
you should call the `clean()` function from time to time.

    Cache::clean();

This way old cache files will be deleted.


##### Pro-Tip

You can use the cache class to clean itself after a specific time. e.g.

    Cache::store("auto-cleanup", function(){
        Cache::clean();
    }, '1 hour');


### Deleting cache files manually

Sometimes it becomes necessary to delete caches immediately. To do so you can call
`trash()` and `trashAll()`.

    // Delete just a single file
    Cache::trash('ifconfig');
    // Delete the whole cache
    Cache::trashAll();
