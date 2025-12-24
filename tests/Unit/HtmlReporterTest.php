<?php

use Sufyan\MigrationLinter\Support\HtmlReporter;
use Sufyan\MigrationLinter\Support\Issue;

describe('HtmlReporter', function () {
    
    it('generates HTML report with no issues', function () {
        $reporter = new HtmlReporter();
        $html = $reporter->generate([]);
        
        expect($html)->toContain('<!DOCTYPE html>')
            ->and($html)->toContain('Laravel Migration Linter Report')
            ->and($html)->toContain('No issues found');
    });

    it('generates HTML report with issues', function () {
        $reporter = new HtmlReporter();
        
        $issue = new Issue(
            ruleId: 'TestRule',
            severity: 'error',
            message: 'Test issue message',
            file: 'test_migration.php',
            line: 10,
            snippet: 'test_column',
            suggestion: 'Test suggestion',
            docsUrl: 'https://docs.example.com'
        );
        
        $html = $reporter->generate([$issue]);
        
        expect($html)->toContain('<!DOCTYPE html>')
            ->and($html)->toContain('Laravel Migration Linter Report')
            ->and($html)->toContain('TestRule')
            ->and($html)->toContain('Test issue message')
            ->and($html)->toContain('test_migration.php');
    });

    it('includes statistics in HTML report', function () {
        $reporter = new HtmlReporter();
        
        $issues = [
            new Issue('Rule1', 'error', 'Error message', 'file1.php', 1, 'col1'),
            new Issue('Rule2', 'warning', 'Warning message', 'file2.php', 2, 'col2'),
            new Issue('Rule3', 'info', 'Info message', 'file3.php', 3, 'col3'),
        ];
        
        $html = $reporter->generate($issues);
        
        expect($html)->toContain('Total Issues')
            ->and($html)->toContain('Files Affected')
            ->and($html)->toContain('Errors')
            ->and($html)->toContain('Warnings')
            ->and($html)->toContain('Info');
    });

    it('groups suggestions by rule', function () {
        $reporter = new HtmlReporter();
        
        $issues = [
            new Issue('SameRule', 'warning', 'Issue 1', 'file1.php', 1, 'col1', 'Same suggestion'),
            new Issue('SameRule', 'warning', 'Issue 2', 'file2.php', 2, 'col2', 'Same suggestion'),
            new Issue('DifferentRule', 'error', 'Issue 3', 'file3.php', 3, 'col3', 'Different suggestion'),
        ];
        
        $html = $reporter->generate($issues);
        
        expect($html)->toContain('Suggestions & Recommendations')
            ->and($html)->toContain('SameRule')
            ->and($html)->toContain('2 occurrence(s)')
            ->and($html)->toContain('DifferentRule')
            ->and($html)->toContain('1 occurrence(s)');
    });

    it('includes filtering functionality', function () {
        $reporter = new HtmlReporter();
        
        $issues = [
            new Issue('Rule1', 'error', 'Error', 'file.php'),
            new Issue('Rule2', 'warning', 'Warning', 'file.php'),
        ];
        
        $html = $reporter->generate($issues);
        
        expect($html)->toContain('filter-btn')
            ->and($html)->toContain('data-filter="all"')
            ->and($html)->toContain('data-filter="error"')
            ->and($html)->toContain('data-filter="warning"');
    });

    it('saves HTML report to file', function () {
        $reporter = new HtmlReporter();
        $tempFile = sys_get_temp_dir() . '/test-report-' . time() . '.html';
        
        $issue = new Issue('TestRule', 'warning', 'Test', 'file.php');
        $reporter->generate([$issue], $tempFile);
        
        expect(file_exists($tempFile))->toBeTrue();
        
        $content = file_get_contents($tempFile);
        expect($content)->toContain('Laravel Migration Linter Report')
            ->and($content)->toContain('TestRule');
        
        // Cleanup
        unlink($tempFile);
    });

    it('creates directory if it does not exist', function () {
        $reporter = new HtmlReporter();
        $tempDir = sys_get_temp_dir() . '/test-dir-' . time();
        $tempFile = $tempDir . '/report.html';
        
        $issue = new Issue('TestRule', 'warning', 'Test', 'file.php');
        $reporter->generate([$issue], $tempFile);
        
        expect(file_exists($tempFile))->toBeTrue();
        expect(is_dir($tempDir))->toBeTrue();
        
        // Cleanup
        unlink($tempFile);
        rmdir($tempDir);
    });

    it('includes chart visualization', function () {
        $reporter = new HtmlReporter();
        
        $issues = [
            new Issue('Rule1', 'error', 'Error', 'file.php'),
            new Issue('Rule2', 'warning', 'Warning', 'file.php'),
        ];
        
        $html = $reporter->generate($issues);
        
        expect($html)->toContain('Issue Distribution')
            ->and($html)->toContain('bar-chart');
    });

    it('includes rule breakdown table', function () {
        $reporter = new HtmlReporter();
        
        $issues = [
            new Issue('Rule1', 'error', 'Error 1', 'file.php'),
            new Issue('Rule1', 'warning', 'Warning 1', 'file.php'),
            new Issue('Rule2', 'error', 'Error 2', 'file.php'),
        ];
        
        $html = $reporter->generate($issues);
        
        expect($html)->toContain('Rule Breakdown')
            ->and($html)->toContain('Rule1')
            ->and($html)->toContain('Rule2');
    });

    it('handles special characters in messages', function () {
        $reporter = new HtmlReporter();
        
        $issue = new Issue(
            'TestRule',
            'error',
            'Message with <script>alert("XSS")</script>',
            'file.php'
        );
        
        $html = $reporter->generate([$issue]);
        
        expect($html)->not->toContain('<script>alert("XSS")</script>')
            ->and($html)->toContain('&lt;script&gt;');
    });

    it('includes responsive design styles', function () {
        $reporter = new HtmlReporter();
        $html = $reporter->generate([]);
        
        expect($html)->toContain('@media')
            ->and($html)->toContain('max-width: 768px');
    });

    it('includes JavaScript for interactivity', function () {
        $reporter = new HtmlReporter();
        $html = $reporter->generate([]);
        
        expect($html)->toContain('<script>')
            ->and($html)->toContain('addEventListener')
            ->and($html)->toContain('searchInput');
    });
});
