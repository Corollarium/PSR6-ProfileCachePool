# PSR6-ProfileCachePool
A PSR6 compatible cache pool for profiling cache access, hits and misses.

## How to use it

```php
$pool = new \Cache\Wrapper\ProfileCachePool(new MyRealAdapterPool());

// use normally
$pool->getItem('foo');

// print cute statistics in the end
$pool->reportHTML();
```


## Aggregating results

Using multiple cache adapters? It's easy to get the aggregated results.

```php
$pool1 = new \Cache\Wrapper\ProfileCachePool(new MyRealAdapterPool());
$pool2 = new \Cache\Wrapper\ProfileCachePool(new MyOtherAdapterPool());

// use normally
$pool1->getItem('foo');
$pool2->getItem('bar');

// print cuter statistics in the end
\Cache\Wrapper\ProfileCachePool::summaryHTML(
	\Cache\Wrapper\ProfileCachePool::mergeProfileSummaries(
		[$pool1, $pool2]
	)
);

$pool->reportHTML();
```

