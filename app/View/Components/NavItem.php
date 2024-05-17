<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NavItem extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public string $href = '#')
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $isActive = self::shouldActive(request()->fullUrl(), $this->href);
        return view('components.nav-item', [
            'href' => $this->href,
            'isActive' => $isActive,
        ]);
    }

    public static function shouldActive(string $currentURL, string $href): bool
    {
        // check if path is same
        // check if href have query string, and the query string exist in the current url
        $pathHref = parse_url($href, PHP_URL_PATH);
        $pathRequest = parse_url($currentURL, PHP_URL_PATH);
        $isActive = $pathHref === $pathRequest;
        $query = request()->query();
        $queryHref = parse_url($href, PHP_URL_QUERY);


        $queryFromHref = [];
        if ($queryHref) {
            $queryHref = explode('&', $queryHref);
        } else {
            $queryHref = [];
        }
        foreach ($queryHref as $key => $value) {
            $res = explode('=', $value);
            if (count($res) === 2) {
                $queryFromHref[$res[0]] = $res[1];
            } else {
                $queryFromHref[$res[0]] = null;
            }
        }
        // intersect
        $intersect = array_intersect($query, $queryFromHref);

        if (count($intersect) !== count($queryFromHref)) {
            $isActive = false;
        }
        return $isActive;
    }
}
