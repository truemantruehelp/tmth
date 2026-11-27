<?php

function workflowFields(array $fields): array
{
    $values = [];
    foreach ($fields as $key => [$label, $limit, $required]) {
        $value = trim((string) filter_input(INPUT_POST, $key));
        if (($required && $value === '') ||
            !preg_match('/\A[^\x00]{0,' . $limit . '}\z/us', $value)) {
            throw new InvalidArgumentException($label);
        }
        $values[$key] = $value === '' ? null : $value;
    }
    return $values;
}

function workflowDate(?string $value): bool
{
    if ($value === null) {
        return true;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value && $value >= '1000-01-01';
}

function workflowFieldInputs(array $fields, array $values, bool $bootstrap = false): void
{
    foreach ($fields as $key => [$label, $limit, $required, $type]) {
        $value = programEscape($values[$key] ?? '');
        $attributes = ($bootstrap ? ' class="form-control"' : '') . ' name="' . $key . '" maxlength="' . $limit . '"' . ($required ? ' required' : '');
        echo '<label>' . programEscape($label);
        if ($type === 'textarea') {
            echo '<textarea' . $attributes . '>' . $value . '</textarea>';
        } else {
            echo '<input type="' . $type . '"' . $attributes . ' value="' . $value . '">';
        }
        echo '</label>';
    }
}
