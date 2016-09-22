<?php

/**
 * PHP Cache Profiler.
 *
 * This is a wrapper class for cache pools that measures cache hits, misses, etc.
 *
 * Developed by Corollarium Technologies
 * https://corollarium.com
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\Wrapper;

class ProfileCachePool implements Psr\Cache\CacheItemPoolInterface
{

    const ACCESSED = 'accessed';

    const MISSED = 'missed';

    const DELETED = 'deleted';

    const CLEANED = 'cleaned';

    const SAVED = 'saved';

    /**
     * Array for basic cache profiling.
     * Keys are Consts of this class, values are counters.
     *
     * @var array
     */
    protected $profile = array(
        self::ACCESSED => 0,
        self::MISSED => 0,
        self::DELETED => 0,
        self::CLEANED => 0,
        self::SAVED => 0
    );

    /**
     * Returns basic cache statistics.
     * See $summary.
     *
     * @return array()
     */
    public function getProfileSummary()
    {
        return $this->profile;
    }

    /**
     *
     * @return number[]
     */
    private function zeroStructure() {
        return array(
            self::ACCESSED => 0,
            self::MISSED => 0,
            self::DELETED => 0,
            self::CLEANED => 0,
            self::SAVED => 0
        );
    }

    public function resetProfileSummary()
    {
        $this->profile = $this->zeroStructure();
    }

    /**
     *
     * @param ProfileCachePool[] $poolInterfaces
     */
    public static function mergeProfileSummaries(array $profileInterfaces)
    {
        $total = $this->zeroStructure();

        foreach ($profileInterfaces as $c) {
            if ($c instanceof ProfileCachePool) {
                $s = $c->getProfileSummary();
                foreach ($s as $k => $v) {
                    $total[$k] += $v;
                }
            }
        }
        return $total;
    }

    /**
     * Generates a simple report in HTML. Run this in the footer of your page.
     */
    public function reportHTML()
    {
        echo '<style>
			.cache-success { background-color: #468847; border-radius: 3px; color: #FFF; padding: 2px 4px; }
			.cache-miss { background-color: #B94A48; border-radius: 3px; color: #FFF; padding: 2px 4px; }
			.cache-save { background-color: #0694F8; border-radius: 3px; color: #FFF; padding: 2px 4px; }
			.cache-deleted { background-color: #F89406; border-radius: 3px; color: #FFF; padding: 2px 4px; }
			.cache-cleaned { background-color: #F894F8; border-radius: 3px; color: #FFF; padding: 2px 4px; }
		</style>';
        $name = get_class($this->pool);
        echo '<div class="cache-summary"><h2>Cache ' . $name . ' system</h2>';

        $stats = $this->zeroStructure();
        echo '<ul>';
        foreach ($stats as $key => $val) {
            echo '<li>' . $key . '=' . $val . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    /**
     * Prints a summary
     *
     * @param array $summary An array like the one returned by getProfileSummary()
     */
    public static function summaryHTML($summary)
    {
        echo '<div id="cache-summary">Cache Profile Summary: ';
        foreach ($summary as $key => $val) {
            echo $key . '=>' . $val . ' / ';
        }
        echo '</div>';
    }

    /**
     *
     * @var Psr\Cache\CacheItemPoolInterface
     */
    protected $pool;

    public function __construct(Psr\Cache\CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
    }

    public function getItem($key)
    {
        $item = $this->pool->getItem($key);
        $this->profile[self::ACCESSED] ++;
        if (! $item->isHit()) {
            $this->profile[self::MISSED] ++;
        }
        return $item;
    }

    public function getItems(array $keys = array())
    {
        $items = $this->pool->getItems($keys);
        foreach ($items as $key => $item) {
            $this->profile[self::ACCESSED] ++;
            if (! $item->isHit()) {
                $this->profile[self::MISSED] ++;
            }
        }
        return $items;
    }

    public function hasItem($key)
    {
        return $this->pool->hasItem($key);
    }

    public function clear()
    {
        $retval = $this->pool->clear();
        if ($retval) {
            $this->profile[self::CLEANED] ++;
        }
        return $this->pool->clear();
    }

    public function deleteItem($key)
    {
        $retval = $this->pool->deleteItem($key);
        if ($retval) {
            $this->profile[self::DELETED] ++;
        }
        return $retval;
    }

    public function deleteItems(array $keys)
    {
        $retval = $this->pool->deleteItems($keys);
        foreach ($retval as $r) {
            if ($r) {
                $this->profile[self::DELETED] ++;
            }
        }
        return $retval;
    }

    public function save(Psr\Cache\CacheItemInterface $item)
    {
        $retval = $this->pool->save($item);
        if ($retval) {
            $this->profile[self::SAVED] ++;
        }
        return $retval;
    }

    public function saveDeferred(Psr\Cache\CacheItemInterface $item)
    {
        $retval = $this->pool->save($item);
        if ($retval) {
            // we're not sure it's going to be saved, but let's count it anyway.
            $this->profile[self::SAVED] ++;
        }
        return $retval;
    }

    public function commit()
    {
        return $this->pool->commit();
    }
}