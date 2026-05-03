<?php

namespace App\Services\BankEmailParsers;

use App\Services\BankEmailParsers\Contracts\BankEmailParserInterface;

class ParserResolver
{
    /** @var array<string, BankEmailParserInterface> */
    protected array $parsers = [];

    public function __construct()
    {
        $this->register(new BankMuscatParser());
        $this->register(new GenericBankParser());
    }

    public function register(BankEmailParserInterface $parser): void
    {
        $this->parsers[$parser->key()] = $parser;
    }

    public function get(string $key): ?BankEmailParserInterface
    {
        return $this->parsers[$key] ?? null;
    }

    /**
     * Resolve a parser. Try the explicit key first, fall back to
     * sniffing supports() across registered parsers, then generic.
     */
    public function resolve(?string $preferredKey, string $sender, string $subject, string $body): BankEmailParserInterface
    {
        if ($preferredKey && isset($this->parsers[$preferredKey])) {
            $parser = $this->parsers[$preferredKey];
            if ($parser->key() === 'generic' || $parser->supports($sender, $subject, $body)) {
                return $parser;
            }
        }

        foreach ($this->parsers as $parser) {
            if ($parser->key() === 'generic') {
                continue;
            }
            if ($parser->supports($sender, $subject, $body)) {
                return $parser;
            }
        }

        return $this->parsers['generic'];
    }

    /** @return array<string, BankEmailParserInterface> */
    public function all(): array
    {
        return $this->parsers;
    }
}
